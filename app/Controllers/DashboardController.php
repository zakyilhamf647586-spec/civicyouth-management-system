<?php

namespace App\Controllers;

use App\Models\MemberModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $memberModel = new MemberModel();

        $data = [
            'title'          => 'Dashboard',
            'total_members'  => $memberModel->countAllResults(),
            'active_members' => $memberModel->where('membership_status', 'active')->countAllResults(),
        ];

        return view('dashboard/index', $data);
    }
}