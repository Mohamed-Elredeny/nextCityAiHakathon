-- =============================================================================
-- Assign every Board Member + Partner as Judge AND Mentor on EVERY active team
-- =============================================================================
-- Safe to re-run (uses INSERT IGNORE — duplicates are silently skipped).
-- Targets MySQL / MariaDB.
--
-- WHAT IT DOES
--   1) For every user with user_category = 'board' or 'partner':
--      • Adds a JudgeAssignment for round1 on every active team
--      • Adds a JudgeAssignment for finals on every active team
--      • Adds a MentorAssignment on every active team
--   2) Makes sure they have the 'judge' and 'mentor' Spatie roles
--      (so /judge and /mentor dashboards open for them).
--
-- BEFORE RUNNING
--   • Make sure board/partner users already exist (run BoardAndPartnersSeeder
--     or create them from Admin → People → Partners / Board Members first).
--
-- VERIFY AT END
--   • Last block prints how many assignments + role-rows now exist for them.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1) JUDGE ASSIGNMENTS — Round 1
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO judge_assignments (judge_id, team_id, round, recused, created_at, updated_at)
SELECT u.id, t.id, 'round1', 0, NOW(), NOW()
FROM users u
CROSS JOIN teams t
WHERE u.user_category IN ('board', 'partner')
  AND t.status = 'active';

-- -----------------------------------------------------------------------------
-- 2) JUDGE ASSIGNMENTS — Finals
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO judge_assignments (judge_id, team_id, round, recused, created_at, updated_at)
SELECT u.id, t.id, 'finals', 0, NOW(), NOW()
FROM users u
CROSS JOIN teams t
WHERE u.user_category IN ('board', 'partner')
  AND t.status = 'active';

-- -----------------------------------------------------------------------------
-- 3) MENTOR ASSIGNMENTS
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO mentor_assignments (mentor_id, team_id, created_at, updated_at)
SELECT u.id, t.id, NOW(), NOW()
FROM users u
CROSS JOIN teams t
WHERE u.user_category IN ('board', 'partner')
  AND t.status = 'active';

-- -----------------------------------------------------------------------------
-- 4) SPATIE ROLES — make sure they have 'judge' and 'mentor'
-- -----------------------------------------------------------------------------
-- Insert the 'judge' role assignment for every board/partner who doesn't already have it
INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM users u
CROSS JOIN roles r
WHERE u.user_category IN ('board', 'partner')
  AND r.name = 'judge';

-- Same for 'mentor'
INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM users u
CROSS JOIN roles r
WHERE u.user_category IN ('board', 'partner')
  AND r.name = 'mentor';

-- -----------------------------------------------------------------------------
-- 5) Make sure their accounts are approved (so /login lets them in)
-- -----------------------------------------------------------------------------
UPDATE users
SET registration_status = 'approved',
    approved_at = COALESCE(approved_at, NOW())
WHERE user_category IN ('board', 'partner')
  AND (registration_status IS NULL OR registration_status <> 'approved');

-- -----------------------------------------------------------------------------
-- 6) VERIFY — counts after the run
-- -----------------------------------------------------------------------------
SELECT
    'board+partner users' AS metric,
    COUNT(*) AS count
FROM users
WHERE user_category IN ('board', 'partner');

SELECT
    'active teams' AS metric,
    COUNT(*) AS count
FROM teams
WHERE status = 'active';

SELECT
    'judge_assignments (round1)' AS metric,
    COUNT(*) AS count
FROM judge_assignments ja
JOIN users u ON u.id = ja.judge_id
WHERE u.user_category IN ('board', 'partner')
  AND ja.round = 'round1';

SELECT
    'judge_assignments (finals)' AS metric,
    COUNT(*) AS count
FROM judge_assignments ja
JOIN users u ON u.id = ja.judge_id
WHERE u.user_category IN ('board', 'partner')
  AND ja.round = 'finals';

SELECT
    'mentor_assignments' AS metric,
    COUNT(*) AS count
FROM mentor_assignments ma
JOIN users u ON u.id = ma.mentor_id
WHERE u.user_category IN ('board', 'partner');

-- Per-person breakdown
SELECT
    u.name,
    u.email,
    u.user_category AS category,
    SUM(CASE WHEN ja.round = 'round1' THEN 1 ELSE 0 END) AS round1_judge_slots,
    SUM(CASE WHEN ja.round = 'finals' THEN 1 ELSE 0 END) AS finals_judge_slots,
    (SELECT COUNT(*) FROM mentor_assignments WHERE mentor_id = u.id) AS mentor_teams
FROM users u
LEFT JOIN judge_assignments ja ON ja.judge_id = u.id
WHERE u.user_category IN ('board', 'partner')
GROUP BY u.id, u.name, u.email, u.user_category
ORDER BY u.user_category, u.name;
