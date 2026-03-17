<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $totalPosts = Post::where('company_id', $companyId)->count();
        $channelsConnected = Channel::where('company_id', $companyId)->count();
        $tasksDueToday = Task::where('company_id', $companyId)
            ->whereDate('due_date', today())
            ->count();
        $teamMembers = User::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        return view('dashboard', compact(
            'totalPosts',
            'channelsConnected',
            'tasksDueToday',
            'teamMembers',
        ));
    }
}

