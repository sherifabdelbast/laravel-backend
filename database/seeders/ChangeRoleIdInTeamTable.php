<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ChangeRoleIdInTeamTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        foreach ($projects as $project) {

            $roles = Role::query()
                ->where('project_id', '=', $project->id)
                ->get()
                ->pluck('id')
                ->toArray();

//            admin
            $teams = Team::query()
                ->where('project_id', '=', $project->id)
                ->withoutGlobalScopes()
                ->get();

            if ($teams) {
                foreach ($teams as $team) {
                    $team->update(['role_id' => $roles[1]]);
                    $team->assignRole($roles[1]);
                }
            }

            //owner
            $teamOwner = Team::query()
                ->where('project_id', '=', $project->id)
                ->where('user_id', '=', $project->user_id)
                ->withoutGlobalScopes()
                ->first();

            if ($teamOwner) {
                $teamOwner->update(['role_id' => $roles[0]]);
                $teamOwner->assignRole($roles[0]);
                $teamOwner->assignRole($roles[0]);
            }
        }
    }
}
