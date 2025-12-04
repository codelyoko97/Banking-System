<?php

namespace App\Jobs;

use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketReplyEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public SupportedTicket $ticket;
    public SupportedTicketMessage $message;

    public function __construct(SupportedTicket $ticket, SupportedTicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function handle(): void
    {
          Mail::html("
<!DOCTYPE html>
<html lang='ar'>
<head>
  <meta charset='UTF-8'>
  <style>
    body {
      font-family: Tahoma, Arial, sans-serif;
      background-color: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    .email-container {
      max-width: 600px;
      margin: 20px auto;
      background-color: #ffffff;
      border: 1px solid #dce3eb;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .header {
      background-color: #004080;
      color: #ffffff;
      padding: 15px;
      text-align: center;
    }
    .header h2 {
      margin: 0;
      font-size: 20px;
    }
    .content {
      padding: 20px;
      color: #333333;
      line-height: 1.6;
    }
    .content p {
      margin: 10px 0;
    }
    .footer {
      background-color: #f0f3f7;
      padding: 15px;
      text-align: center;
      font-size: 13px;
      color: #666666;
    }
  </style>
</head>
<body>
  <div class='email-container'>
    <div class='header'>
      <h2>رد جديد على التذكرة #{$this->ticket->id}</h2>
    </div>
    <div class='content'>
      <p>{$this->message->message}</p>
      <p>شكراً لتواصلك معنا.</p>
    </div>
    <div class='footer'>
      فريق الدعم الفني - بنك الحاتم
    </div>
  </div>
</body>
</html>
", function ($message) {
          $message->to($this->ticket->customer->email)
            ->subject("Reply on ticket #{$this->ticket->id}");
        });
    }
}
