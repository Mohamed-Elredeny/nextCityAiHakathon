<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeamAssetController extends Controller
{
    public function uploadLogo(Request $request)
    {
        $team = $this->teamForLeader();
        $request->validate(['logo' => 'required|image|max:1024']);

        if ($team->logo_path) {
            Storage::disk('public')->delete($team->logo_path);
        }
        $team->logo_path = $request->file('logo')->store('team-logos', 'public');
        $team->save();

        return back()->with('asset_status', 'Logo uploaded.');
    }

    public function deleteLogo()
    {
        $team = $this->teamForLeader();
        if ($team->logo_path) {
            Storage::disk('public')->delete($team->logo_path);
            $team->logo_path = null;
            $team->save();
        }
        return back()->with('asset_status', 'Logo removed.');
    }

    public function uploadBanner(Request $request)
    {
        $team = $this->teamForLeader();
        $request->validate(['banner' => 'required|image|max:3072']);

        if ($team->banner_path) {
            Storage::disk('public')->delete($team->banner_path);
        }
        $team->banner_path = $request->file('banner')->store('team-banners', 'public');
        $team->save();

        return back()->with('asset_status', 'Banner uploaded.');
    }

    public function deleteBanner()
    {
        $team = $this->teamForLeader();
        if ($team->banner_path) {
            Storage::disk('public')->delete($team->banner_path);
            $team->banner_path = null;
            $team->save();
        }
        return back()->with('asset_status', 'Banner removed.');
    }

    private function teamForLeader(): Team
    {
        $team = Auth::user()?->currentTeam();
        abort_if(!$team, 404, 'You are not on a team.');
        abort_if(Auth::id() !== $team->leader_id, 403, 'Only the team leader can manage team assets.');
        return $team;
    }
}
