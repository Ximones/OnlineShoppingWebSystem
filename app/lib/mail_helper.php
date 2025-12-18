<?php

require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';

function get_mail() {
    $m = new PHPMailer(true);
    $m->SMTPDebug = 0;
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $m->Username = 'bmit2013lwc@gmail.com';
    $m->Password = 'kmjq wngb dhua unhv';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '🚽 Daily Bowls');
    $m->isHTML(true);
    return $m;
}

function render_ereceipt(array $order, array $user): string
{
    ob_start();
    extract(['order' => $order, 'user' => $user]);
    require __DIR__ . '/../views/orders/ereceipt.php';
    return ob_get_clean();
}