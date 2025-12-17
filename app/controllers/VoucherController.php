<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserVoucher;
use App\Models\Voucher;

class VoucherController extends Controller
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
        $this->requireAuth();
        $userId = auth_id();

        $allVouchers = $this->vouchers->all(['active_only' => true]);
        $userVouchers = $this->userVouchers->allForUser($userId);

        // Map of voucher_id => user status (claimed/used/etc.) for current user
        $claimedByUser = [];
        foreach ($userVouchers as $uv) {
            $claimedByUser[(int) $uv['voucher_id']] = strtolower($uv['status']);
        }

        // Map of voucher_id => total claim count (all users)
        $claimCounts = [];
        foreach ($this->userVouchers->countsByVoucher() as $row) {
            $claimCounts[(int) $row['voucher_id']] = (int) $row['total_claims'];
        }

        $this->render('vouchers/index', [
            'allVouchers'   => $allVouchers,
            'userVouchers'  => $userVouchers,
            'claimedByUser' => $claimedByUser,
            'claimCounts'   => $claimCounts,
        ]);
    }

    public function claim(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $voucherId = (int) post('voucher_id');

        if (!$voucherId) {
            redirect('?module=vouchers&action=index');
        }

        $voucher = $this->vouchers->find($voucherId);
        if (!$voucher || !$voucher['is_active']) {
            flash('danger', 'Voucher not available.');
            redirect('?module=vouchers&action=index');
        }

        $maxClaims = isset($voucher['max_claims']) && $voucher['max_claims'] !== null
            ? (int) $voucher['max_claims']
            : null;

        $result = $this->userVouchers->claim($userId, $voucherId, $maxClaims);
        if ($result === 'ok') {
            flash('success', 'Voucher claimed.');
        } elseif ($result === 'duplicate') {
            flash('danger', 'You have claimed this voucher before.');
        } else { // sold_out
            flash('danger', 'This voucher has been fully redeemed.');
        }

        redirect('?module=vouchers&action=index');
    }
}


