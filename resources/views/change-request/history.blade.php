<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติคำร้องขอปรับปรุงระบบ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Sarabun', sans-serif; background: #f0f4f8; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .page-title { font-size: 20px; font-weight: 700; color: #1e429f; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; border-radius: 8px; font-family: 'Sarabun', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.85; }
        .btn-primary { background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%); color: #fff; }
        .btn-danger { background: #ef4444; color: #fff; font-size: 13px; padding: 6px 14px; }
        .btn-view { background: #f3f4f6; color: #374151; font-size: 13px; padding: 6px 14px; border: 1px solid #d1d5db; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; font-size: 14px; color: #166534; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        th { padding: 14px 16px; text-align: left; font-size: 13px; font-weight: 700; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 14px 16px; font-size: 14px; color: #111827; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9fafb; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .badge-normal   { background: #e0f2fe; color: #0369a1; }
        .badge-urgent   { background: #fef3c7; color: #92400e; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .empty { text-align: center; padding: 60px 20px; color: #9ca3af; font-size: 15px; }
        .pagination { display: flex; justify-content: center; gap: 8px; padding: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 6px 14px; border-radius: 6px; font-size: 14px; border: 1px solid #d1d5db; color: #374151; text-decoration: none; }
        .pagination .active { background: #1a56db; color: #fff; border-color: #1a56db; }
        .actions { display: flex; gap: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="page-title">ประวัติคำร้องขอปรับปรุงแก้ไขระบบ</div>
            <a href="{{ route('change-request.create') }}" class="btn btn-primary">+ สร้างคำร้องใหม่</a>
        </div>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            @if ($records->isEmpty())
                <div class="empty">ยังไม่มีคำร้องในระบบ</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อผู้ขอ</th>
                            <th>หน่วยงาน</th>
                            <th>วันที่ยื่น</th>
                            <th>ระดับความสำคัญ</th>
                            <th>โมดูล</th>
                            <th>วันที่บันทึก</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $priorityLabel = ['normal'=>'ปกติ','urgent'=>'ด่วน','critical'=>'ด่วนที่สุด'];
                            $priorityClass = ['normal'=>'badge-normal','urgent'=>'badge-urgent','critical'=>'badge-critical'];
                        @endphp
                        @foreach ($records as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->requester_name }}</td>
                            <td>{{ $r->department }}</td>
                            <td>{{ $r->request_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $priorityClass[$r->priority] ?? '' }}">
                                    {{ $priorityLabel[$r->priority] ?? $r->priority }}
                                </span>
                            </td>
                            <td>{{ $r->module_name }}</td>
                            <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('change-request.show', $r->id) }}" class="btn btn-view">ดู</a>
                                    <form method="POST" action="{{ route('change-request.destroy', $r->id) }}"
                                          onsubmit="return confirm('ลบรายการนี้?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>