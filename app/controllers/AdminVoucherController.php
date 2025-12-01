<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Voucher;

class AdminVoucherController extends Controller
{
    private Voucher $vouchers;

    public function __construct()
    {
        $this->vouchers = new Voucher();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $search = get('keyword', '');
        $vouchers = $this->vouchers->all(['search' => $search]);
        $this->render('admin/vouchers/index', compact('vouchers', 'search'));
    }

    public function create(): void
    {
        $this->requireAdmin();
        if (is_post() && validate([
            'code' => ['required' => 'Code is required.'],
            'name' => ['required' => 'Name is required.'],
            'type' => ['required' => 'Type is required.'],
            'value' => ['required' => 'Value is required.'],
        ])) {
            $this->vouchers->create([
                'code' => post('code'),
                'name' => post('name'),
                'description' => post('description'),
                'type' => post('type'),
                'value' => (float) post('value'),
                'min_subtotal' => (float) post('min_subtotal', 0),
                'max_discount' => post('max_discount') !== '' ? (float) post('max_discount') : null,
                'max_claims' => post('max_claims') !== '' ? (int) post('max_claims') : null,
                'is_shipping_only' => post('is_shipping_only') ? 1 : 0,
                'is_first_order_only' => post('is_first_order_only') ? 1 : 0,
                'start_at' => post('start_at'),
                'end_at' => post('end_at'),
                'is_active' => post('is_active') ? 1 : 0,
            ]);
            flash('success', 'Voucher created.');
            redirect('?module=admin&resource=vouchers&action=index');
        }

        $this->render('admin/vouchers/form');
    }
}


