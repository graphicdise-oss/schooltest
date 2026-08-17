<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Tahoma, sans-serif; background:#f4f6f9; padding:24px;">
    <div style="max-width:560px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06);">
        <div style="background:#082b75; color:#fff; padding:18px 24px; font-size:1.05rem; font-weight:700;">
            แจ้งปัญหา/ติดต่อจากระบบ School Tech
        </div>
        <div style="padding:24px;">
            <table style="width:100%; border-collapse:collapse; font-size:0.92rem; color:#333;">
                <tr>
                    <td style="padding:6px 0; color:#777; width:110px; vertical-align:top;">ชื่อผู้แจ้ง</td>
                    <td style="padding:6px 0;">{{ $contactMessage->name }}</td>
                </tr>
                @if($contactMessage->email)
                <tr>
                    <td style="padding:6px 0; color:#777; vertical-align:top;">อีเมลติดต่อกลับ</td>
                    <td style="padding:6px 0;">{{ $contactMessage->email }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0; color:#777; vertical-align:top;">หัวข้อ</td>
                    <td style="padding:6px 0; font-weight:600;">{{ $contactMessage->subject }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0; color:#777; vertical-align:top;">วันที่แจ้ง</td>
                    <td style="padding:6px 0;">{{ $contactMessage->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
            <div style="margin-top:18px; padding-top:16px; border-top:1px solid #eee; white-space:pre-wrap; font-size:0.92rem; color:#333; line-height:1.6;">{{ $contactMessage->message }}</div>
        </div>
    </div>
</body>
</html>
