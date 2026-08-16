<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminApprovalController extends Controller
{
    /**
     * 【PG12】全スタッフから提出された修正申請の承認待ち一覧表示
     */
    public function requestList(Request $request)
    {
        $status = $request->input('status', 'pending');
        $requests = AttendanceRequest::with('user')->where('status', $status)->orderBy('date', 'desc')->get();
        return view('admin.request_list', compact('requests', 'status'));
    }

    /**
     * 【PG13】申請内容の確認画面の表示
     */
    public function approveView($id)
    {
        $requestData = AttendanceRequest::with(['user', 'attendance'])->findOrFail($id);
        $appliedBreaks = json_decode($requestData->break_times, true) ?: [];
        return view('admin.approve_view', compact('requestData', 'appliedBreaks'));
    }

    /**
     * 【PG13】申請の承認・却下アクション処理（本データへのマージ同期）
     */
    public function approveAction(Request $request, $id)
    {
        $attendanceRequest = AttendanceRequest::findOrFail($id);

        if ($request->input('action') === 'approve') {
            // トランザクションで安全に本データを一括上書き更新
            \DB::transaction(function () use ($attendanceRequest) {
                $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);
                $attendance->update([
                    'clock_in' => $attendanceRequest->clock_in,
                    'clock_out' => $attendanceRequest->clock_out,
                ]);

                // 既存の古い休憩実績を一度お片付けして、申請された新しい休憩データに差し替え
                $attendance->breakTimes()->delete();
                $appliedBreaks = json_decode($attendanceRequest->break_times, true) ?: [];
                foreach ($appliedBreaks as $b) {
                    if (!empty($b['break_in']) && !empty($b['break_out'])) {
                        $attendance->breakTimes()->create([
                            'break_in' => $b['break_in'],
                            'break_out' => $b['break_out'],
                        ]);
                    }
                }

                $attendanceRequest->update(['status' => 'approved']);
            });
        }

        return redirect()->route('admin.request_list')->with('success', '申請の処理が完了しました。');
    }
}
