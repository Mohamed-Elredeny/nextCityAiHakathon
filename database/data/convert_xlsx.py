"""
Convert the registration xlsx into the teams.json format consumed by
TeamsImportSeeder. Run from project root:

  python database/data/convert_xlsx.py "public/NextCity AI Hack - Registration Form(1-123) (1).xlsx"
"""
import json
import re
import sys
from datetime import datetime
from pathlib import Path
from collections import OrderedDict

import openpyxl

XLSX = sys.argv[1] if len(sys.argv) > 1 else \
    "public/NextCity AI Hack – Registration Form(1-123) (1).xlsx"
OUT = Path("database/data/teams.json")

HDR_MAP = {
    "Email":                                                                "email_login",
    "Name":                                                                 "short_name",
    "Full Name":                                                            "full_name",
    "Email2":                                                               "email_alt",
    "Mobile Number":                                                        "phone",
    "National ID":                                                          "national_id",
    "Student ID":                                                           "student_id",
    "Current Status":                                                       "status_kind",
    "Institution / Company Name":                                           "institution",
    "Field of Study / Work":                                                "field",
    "Academic Year (if student)":                                           "academic_year",
    "LinkedIn Profile":                                                     "linkedin",
    "Portfolio / GitHub / Website":                                         "portfolio",
    "Primary Skills (Select all that apply)":                               "skills",
    "Skill Level":                                                          "skill_level",
    "Briefly describe your relevant experience (max 150 words)":            "experience",
    "Are you applying as:":                                                 "applying_as",
    "If Team: write Team Name, Number of Members, Team Members Names":     "team_text",
    "Preferred Role in Team:":                                              "role_in_team",
    "Select your preferred track:":                                         "track",
    "Problem Statement / Idea":                                             "problem",
    "Completion time":                                                      "submitted_at",
}


def norm(s):
    if s is None:
        return ""
    return re.sub(r"\s+", " ", str(s)).strip()


_EMAIL_RE = re.compile(r"^[^@\s]+@[^@\s]+\.[^@\s]+$")


def best_email(row):
    candidates = [norm(row.get("email_alt")), norm(row.get("email_login"))]
    for c in candidates:
        if c and _EMAIL_RE.match(c):
            return c.lower()
    # No valid email anywhere — return first non-empty so caller can fallback.
    for c in candidates:
        if c:
            return c.lower()
    return ""


_NOISE_LINE = re.compile(
    r"^(no\.?|number|num|count|members?|team\s*members?(\s*names?)?|"
    r"team\s*member|members?\s*names?)\b",
    re.I,
)
_DIGIT_ONLY = re.compile(r"^[\d\s\-+()/.]+$")


def _clean_candidate(cand: str) -> str:
    cand = cand.strip(" \t.,;:_-").strip()
    # Stop at "Number of Members" / "X Members" suffix
    cand = re.split(
        r"\s+(?:no\.?|number|num|count|members?)\b",
        cand,
        maxsplit=1,
        flags=re.I,
    )[0].strip(" \t.,;:_-")
    # Strip parenthetical at end "EcoMind AI (2)"
    cand = re.sub(r"\s*\(\s*\d+\s*\)\s*$", "", cand).strip()
    return cand


def parse_team_name(text):
    """Extract a clean team name from the free-form 'team_text' field.

    Returns "" if no plausible team name can be found — caller should treat
    those registrants as unaffiliated individuals.
    """
    if not text:
        return ""
    # Preserve newlines; only collapse spaces/tabs.
    raw = str(text).replace("\r\n", "\n").replace("\r", "\n")
    raw = "\n".join(re.sub(r"[ \t]+", " ", ln).strip() for ln in raw.split("\n"))
    raw = raw.replace("‏", "").replace("‎", "").strip()
    if not raw:
        return ""

    # Strong patterns: explicit "team name: X", "Name=X", "Name of team: X".
    # Use [ \t]* (not \s*) after the separator so the capture stays on-line.
    patterns = [
        r"team\s*name\s*[:=\-][ \t]*([^\n]+)",
        r"name\s*of\s*team\s*[:=\-][ \t]*([^\n]+)",
        r"^[ \t]*team[ \t]*[:=][ \t]*([^\n]+)",
        r"^[ \t]*name[ \t]*[:=][ \t]*([^\n]+)",
    ]
    for pat in patterns:
        m = re.search(pat, raw, re.I | re.M)
        if m:
            cand = _clean_candidate(m.group(1))
            if cand:
                return cand

    lines = [ln for ln in raw.split("\n") if ln.strip()]
    if not lines:
        return ""

    # First line as candidate (handle "Name, count, members")
    first = lines[0]
    if "," in first:
        head, tail = first.split(",", 1)
    else:
        head, tail = first, ""
    cand = _clean_candidate(head)
    if cand \
            and not _DIGIT_ONLY.match(cand) \
            and not _NOISE_LINE.match(cand) \
            and not re.match(r"^\d+[\.\)\-]", cand) \
            and not re.match(r"^number\s+of\b", cand, re.I) \
            and not re.match(r"^team\s+name", cand, re.I):
        rest = lines[1:]
        # Supportive: either following lines look like member rosters,
        # or the rest of the first line itself contains a count/names.
        looks_supportive = any(
            re.search(r"\b(member|members|number)\b", ln, re.I)
            or _DIGIT_ONLY.match(ln)
            or re.match(r"^\d+[\.\)\-]", ln)
            for ln in rest
        )
        if not looks_supportive and tail.strip():
            looks_supportive = bool(
                re.search(r"\b(member|members|number)\b", tail, re.I)
                or re.search(r"\d", tail)
                or len([p for p in tail.split(",") if p.strip()]) >= 2
            )
        if looks_supportive:
            return cand

    return ""


_TEAM_SUFFIX = re.compile(r"\s*team\s*$", re.I)


def slugify_key(name):
    """Group key — lowercase alphanumeric; trailing 'team' suffix dropped so
    'Aoun team' merges with 'Aoun'."""
    base = _TEAM_SUFFIX.sub("", name).strip()
    if not base:
        base = name
    return re.sub(r"[^a-z0-9]+", "", base.lower())


def main():
    wb = openpyxl.load_workbook(XLSX, data_only=True)
    ws = wb.worksheets[0]
    rows_iter = ws.iter_rows(values_only=True)
    header = next(rows_iter)
    idx = {h: header.index(h) for h in HDR_MAP if h in header}

    parsed = []
    for raw in rows_iter:
        if not raw or all(c is None for c in raw):
            continue
        rec = {}
        for h, key in HDR_MAP.items():
            if h in idx:
                rec[key] = raw[idx[h]]
        # normalize submitted_at to "YYYY-MM-DD HH:MM:SS"
        sa = rec.get("submitted_at")
        if isinstance(sa, datetime):
            rec["submitted_at"] = sa.strftime("%Y-%m-%d %H:%M:%S")
        else:
            rec["submitted_at"] = norm(sa)

        rec["full_name"] = norm(rec.get("full_name") or rec.get("short_name") or "")
        rec["email"] = best_email(rec)
        rec["phone"] = norm(rec.get("phone"))
        rec["national_id"] = re.sub(r"\D+", "", str(rec.get("national_id") or ""))
        rec["student_id"] = norm(rec.get("student_id"))
        rec["institution"] = norm(rec.get("institution"))
        rec["field"] = norm(rec.get("field"))
        rec["role_in_team"] = norm(rec.get("role_in_team"))
        rec["track"] = norm(rec.get("track"))
        rec["skills"] = norm(rec.get("skills"))
        rec["skill_level"] = norm(rec.get("skill_level"))
        rec["problem"] = norm(rec.get("problem"))
        rec["applying_as"] = norm(rec.get("applying_as"))

        parsed.append(rec)

    teams = OrderedDict()  # group_key -> {"team_name": display, "registrants": [...]}
    unaffiliated = []
    skipped = []

    for rec in parsed:
        applying = rec["applying_as"].lower()
        team_name = ""
        if "team" in applying:
            team_name = parse_team_name(rec.get("team_text"))

        if team_name:
            key = slugify_key(team_name)
            if key not in teams:
                teams[key] = {"team_name": team_name, "registrants": [], "declared_members": []}
            else:
                # prefer the longer / more capitalised display name
                if len(team_name) > len(teams[key]["team_name"]):
                    teams[key]["team_name"] = team_name

            registrant = {k: rec.get(k, "") for k in [
                "full_name", "email", "phone", "national_id", "student_id",
                "institution", "field", "role_in_team", "track",
                "skills", "skill_level", "submitted_at", "problem", "applying_as",
            ]}
            teams[key]["registrants"].append(registrant)
        else:
            if not rec["email"]:
                skipped.append(rec.get("full_name", "(no name)"))
                continue
            unaffiliated.append({k: rec.get(k, "") for k in [
                "full_name", "email", "phone", "national_id", "student_id",
                "institution", "field", "role_in_team", "track",
                "skills", "skill_level", "submitted_at", "problem", "applying_as",
            ]})

    out = {
        "teams": [v for v in teams.values()],
        "unaffiliated": unaffiliated,
    }

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(out, ensure_ascii=False, indent=2), encoding="utf-8")

    summary_lines = [
        f"Parsed registrants: {len(parsed)}",
        f"Teams:              {len(out['teams'])}",
        f"Unaffiliated:       {len(out['unaffiliated'])}",
        f"Skipped (no email & not team): {len(skipped)}",
        f"Wrote -> {OUT}",
        "",
        "Team roster:",
    ]
    for t in out["teams"]:
        emails = ", ".join(r["email"] for r in t["registrants"])
        summary_lines.append(f"  - {t['team_name']} ({len(t['registrants'])}): {emails}")

    summary = "\n".join(summary_lines)
    Path("database/data/_import_summary.txt").write_text(summary, encoding="utf-8")
    try:
        print(summary)
    except UnicodeEncodeError:
        print(summary.encode("ascii", "replace").decode("ascii"))


if __name__ == "__main__":
    main()
