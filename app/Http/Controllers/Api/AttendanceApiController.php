<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    public function index()
    {
        $paginated = Attendance::with('breakTimes')->paginate(10);
        
        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function show($id)
    {
        $attendance = Attendance::with('breakTimes')->find($id);
        
        if (!$attendance) {
            return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404, [], JSON_UNESCAPED_UNICODE);
        }
        
        return response()->json(['data' => $attendance], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'clock_in' => 'required',
        ], [
            'date.required' => '日付は必須項目です。',
            'clock_in.required' => '出勤時間は必須項目です。',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $attendance = Attendance::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
        ]);

        return response()->json($attendance, 201);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($attendance->user_id !== auth()->id()) {
            return response()->json(['error' => 'この操作を実行する権限がありません。'], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $attendance->update($request->only(['clock_in', 'clock_out']));

        return response()->json(['message' => 'レコードが更新されました。'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($attendance->user_id !== auth()->id()) {
            return response()->json(['error' => 'この操作を実行する権限がありません。'], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $attendance->delete();

        return response(null, 204);
    }
}
