<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Voucher;
use App\Models\UserVoucher;

class AdminVoucherController extends AdminController
{
    private Voucher $vouchers;
    private UserVoucher $userVouchers;

    public function __construct()
    {
        $this->vouchers = new Voucher();
        $this->userVouchers = new UserVoucher();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $search = get('keyword', '');
        $vouchers = $this->vouchers->all(['search' => $search]);

        // Get claim counts for each voucher
        $claimCounts = [];
        foreach ($this->userVouchers->countsByVoucher() as $row) {
            $claimCounts[(int) $row['voucher_id']] = (int) $row['total_claims'];
        }

        $this->render('admin/vouchers/index', compact('vouchers', 'search', 'claimCounts'));
    }

    public function create(): void
    {
        $this->requireAdmin();

        if (is_post()) {
            // [NEW] 1. Duplicate Check Logic
            // We check this BEFORE standard validation to fail fast.
            $code = trim(post('code'));
            $existing = $this->vouchers->findByCode($code);

            if ($existing) {
                flash('danger', "The voucher code '$code' is already in use. Please use a unique code.");
                // We render the form immediately so the user can fix it.
                // Sticky inputs (html_text) will keep the values they typed.
                $this->render('admin/vouchers/form');
                return;
            }

            // 2. Standard Validation
            if (validate([
                'code' => ['required' => 'Code is required.'],
                'name' => ['required' => 'Name is required.'],
                'type' => ['required' => 'Type is required.'],
                'value' => ['required' => 'Value is required.', 'numeric' => 'Value must be a number.'],
                'min_subtotal' => ['numeric' => 'Min spend must be a number.'],
                'max_discount' => ['numeric' => 'Max discount must be a number.'],
                'quota' => ['numeric' => 'Quota must be a number.'],
            ])) {
                $this->vouchers->create([
                    'code' => strtoupper(post('code')),
                    'name' => post('name'),
                    'description' => post('description'),
                    'type' => post('type'),
                    'value' => post('value'),
                    'min_subtotal' => post('min_subtotal') ?: 0,
                    'max_discount' => post('max_discount') ?: null,
                    'quota' => post('quota') ?: 0,
                    'is_active' => post('is_active') ? 1 : 0,
                    'is_shipping_only' => post('is_shipping_only') ? 1 : 0,
                    'is_first_order_only' => post('is_first_order_only') ? 1 : 0,
                    'valid_from' => post('valid_from') ?: null,
                    'valid_until' => post('valid_until') ?: null,
                ]);
                flash('success', 'Voucher created.');
                redirect('?module=admin&resource=vouchers&action=index');
            }
        }

        $this->render('admin/vouchers/form');
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) get('id');

        if (is_post()) {
            // [NEW] 1. Duplicate Check Logic (Specific for Edit)
            $code = trim(post('code'));
            $existing = $this->vouchers->findByCode($code);

            // If code exists AND it belongs to a different voucher ID
            if ($existing && $existing['id'] !== $id) {
                flash('danger', "The voucher code '$code' is already taken by another voucher.");

                // Fetch the original voucher data so the form context doesn't break
                $voucher = $this->vouchers->find($id);
                $this->render('admin/vouchers/form', compact('voucher'));
                return;
            }

            // 2. Standard Validation
            if (validate([
                'code' => ['required' => 'Code is required.'],
                'name' => ['required' => 'Name is required.'],
                'type' => ['required' => 'Type is required.'],
                'value' => ['required' => 'Value is required.', 'numeric' => 'Value must be a number.'],
                'min_subtotal' => ['numeric' => 'Min spend must be a number.'],
                'max_discount' => ['numeric' => 'Max discount must be a number.'],
                'quota' => ['numeric' => 'Quota must be a number.'],
            ])) {
                $this->vouchers->update($id, [
                    'code' => strtoupper(post('code')),
                    'name' => post('name'),
                    'description' => post('description'),
                    'type' => post('type'),
                    'value' => post('value'),
                    'min_subtotal' => post('min_subtotal') ?: 0,
                    'max_discount' => post('max_discount') ?: null,
                    'quota' => post('quota') ?: 0,
                    'is_active' => post('is_active') ? 1 : 0,
                    'is_shipping_only' => post('is_shipping_only') ? 1 : 0,
                    'is_first_order_only' => post('is_first_order_only') ? 1 : 0,
                    'valid_from' => post('valid_from') ?: null,
                    'valid_until' => post('valid_until') ?: null,
                ]);
                flash('success', 'Voucher updated.');
                redirect('?module=admin&resource=vouchers&action=index');
            }
        }

        $voucher = $this->vouchers->find($id);
        if (!$voucher) {
            flash('danger', 'Voucher not found.');
            redirect('?module=admin&resource=vouchers&action=index');
        }

        $this->render('admin/vouchers/form', compact('voucher'));
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        $this->vouchers->delete($id);
        flash('success', 'Voucher deleted.');
        redirect('?module=admin&resource=vouchers&action=index');
    }

    public function batchDelete(): void
    {
        $this->requireAdmin();
        $ids = array_map('intval', post('ids', []));
        if (empty($ids)) {
            flash('danger', 'No vouchers selected.');
            redirect('?module=admin&resource=vouchers&action=index');
        }
        $this->vouchers->batchDelete($ids);
        flash('success', count($ids) . ' voucher(s) deleted.');
        redirect('?module=admin&resource=vouchers&action=index');
    }

    public function check_VoucherCode(): void
    {
        // Get parameters
        $code = trim($_GET['code'] ?? '');
        $excludeId = (int)($_GET['id'] ?? 0); // For edit mode (ignore self)

        if ($code === '') {
            echo json_encode(['exists' => false]);
            exit;
        }

        // Check DB
        $voucher = $this->vouchers->findByCode($code);

        // Exists if found AND it's not the one we are currently editing
        $exists = $voucher && ((int)$voucher['id'] !== $excludeId);

        // Return JSON
        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
        exit; // Stop execution (don't render a view)
    }
}
