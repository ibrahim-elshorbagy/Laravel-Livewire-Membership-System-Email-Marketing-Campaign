<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Email\EmailMessage;

class EmailMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmailMessage::create([
            'user_id'          => 2,
            'email_subject'    => "Don't Miss Out on Our Summer Sale!",
            'message_html'     => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Summer Sale at Awesome Store!</title>
  <style>
    /* Reset */
    body, html {
      margin: 0;
      padding: 0;
    }
    body {
      background-color: #f7f7f7;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #333;
      line-height: 1.6;
    }
    .container {
      max-width: 600px;
      margin: 20px auto;
      background: #fff;
      border: 1px solid #e5e5e5;
      border-radius: 4px;
      overflow: hidden;
    }
    .header {
      background: #ff6f61;
      padding: 20px;
      text-align: center;
      color: #fff;
    }
    .header img {
      max-width: 80px;
      margin-bottom: 10px;
    }
    .header h1 {
      margin: 0;
      font-size: 28px;
    }
    .content {
      padding: 20px;
    }
    .content h2 {
      color: #ff6f61;
      font-size: 24px;
      margin-top: 0;
    }
    .content p {
      margin: 15px 0;
      font-size: 16px;
    }
    .cta {
      text-align: center;
      margin: 30px 0;
    }
    .cta a {
      background: #ff6f61;
      color: #fff;
      padding: 15px 25px;
      text-decoration: none;
      border-radius: 4px;
      font-size: 16px;
    }
    .footer {
      background: #f1f1f1;
      padding: 15px;
      text-align: center;
      font-size: 12px;
      color: #777;
    }
    .footer a {
      color: #ff6f61;
      text-decoration: none;
    }
    @media only screen and (max-width: 600px) {
      .container {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/Bitmap_VS_SVG.svg" alt="Awesome Store Logo">
      <h1>Awesome Store</h1>
    </div>
    <div class="content">
      <h2>Summer Sale is Here!</h2>
      <p>Dear Valued Customer,</p>
      <p>We're excited to announce our biggest summer sale ever! Enjoy discounts up to <strong>50% off</strong> on selected items. Whether you're shopping for trendy apparel, accessories, or home decor, we've got incredible deals waiting for you.</p>
      <div class="cta">
        <a>Shop Now</a>
      </div>
      <p>Hurry—this exclusive sale lasts until the end of August. Don’t miss your chance to grab these amazing deals!</p>
      <p>Thank you for being a loyal customer.</p>
      <p>Best regards,<br>Awesome Store Team</p>
    </div>
    <div class="footer">
      <p>Awesome Store, 123 Market Street, City, Country</p>
      <p>If you no longer wish to receive our emails, you can <a href="https://awesomestore.com/unsubscribe">unsubscribe here</a>.</p>
    </div>
  </div>
</body>
</html>
HTML,
            'message_plain_text' => <<<'TEXT'
Awesome Store Summer Sale!

Dear Valued Customer,

We're excited to announce our biggest summer sale ever! Enjoy discounts up to 50% off on selected items. Shop for trendy apparel, accessories, and home decor at unbeatable prices.

Shop Now: https://awesomestore.com/sale

Hurry—this exclusive sale lasts until the end of August. Don’t miss out on these amazing deals!

Thank you for being a loyal customer.

Best regards,
Awesome Store Team

Awesome Store, 123 Market Street, City, Country
Unsubscribe: https://awesomestore.com/unsubscribe
TEXT,

        ]);
    }
}
