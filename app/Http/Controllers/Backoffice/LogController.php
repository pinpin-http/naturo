<?php

namespace App\Http\Controllers\Backoffice;
use App\Models\UserActionLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = UserActionLog::with('user')->orderBy('created_at', 'desc')->paginate(24);
        return view('backoffice.logs.index', compact('logs'));
    }
}
