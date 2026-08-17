<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    // อีเมลผู้รับแจ้งปัญหา/ติดต่อ (ผู้ดูแลระบบ)
    private const RECIPIENT_EMAIL = 'graphicdise@gmail.com';

    public function create()
    {
        $user = Auth::user();
        return view('contact.create', [
            'defaultName'  => trim(($user->thai_prefix ?? '') . ($user->thai_firstname ?? '') . ' ' . ($user->thai_lastname ?? '')),
            'defaultEmail' => $user->email ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'nullable|email|max:150',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ], [
            'name.required'    => 'กรุณากรอกชื่อผู้แจ้ง',
            'subject.required' => 'กรุณากรอกหัวข้อ',
            'message.required' => 'กรุณากรอกรายละเอียด',
            'email.email'      => 'รูปแบบอีเมลไม่ถูกต้อง',
        ]);

        $contactMessage = ContactMessage::create([
            'personnel_id' => Auth::user()->personnel_id ?? null,
            'name'         => $data['name'],
            'email'        => $data['email'] ?? null,
            'subject'      => $data['subject'],
            'message'      => $data['message'],
        ]);

        try {
            Mail::to(self::RECIPIENT_EMAIL)->send(new ContactMessageMail($contactMessage));
            $contactMessage->update(['email_sent' => true]);
            $status = 'success';
        } catch (\Throwable $e) {
            report($e);
            $status = 'saved_only';
        }

        return back()->with($status, $status === 'success'
            ? 'ส่งข้อความแจ้งปัญหาสำเร็จ ทีมงานจะติดต่อกลับโดยเร็วที่สุด'
            : 'บันทึกข้อความเรียบร้อยแล้ว แต่ส่งอีเมลแจ้งเตือนไม่สำเร็จ (ระบบยังบันทึกข้อมูลไว้ให้ผู้ดูแลระบบเห็นแล้ว)');
    }
}
