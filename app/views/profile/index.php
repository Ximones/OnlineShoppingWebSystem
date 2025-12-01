<?php
$title = 'My Profile';
$checkInPoints = $checkInPoints ?? [
    1 => 1,
    2 => 5,
    3 => 10,
    4 => 15,
    5 => 20,
    6 => 25,
    7 => 100,
];
$streak = max(0, (int) ($user['check_in_streak'] ?? 0));
$lastCheckInAt = $user['last_check_in_at'] ?? null;
$today = new DateTimeImmutable('today');
$lastCheckInDate = $lastCheckInAt ? (new DateTimeImmutable($lastCheckInAt))->setTime(0, 0) : null;
$checkedInToday = $lastCheckInDate && $lastCheckInDate->format('Y-m-d') === $today->format('Y-m-d');
$nextStreakForReward = $streak ? min(7, $streak + 1) : 1;
$currentReward = $checkInPoints[$nextStreakForReward];
?>
<section class="panel" style="max-width: 1200px; margin: 0 auto;">
    <div class="profile-layout">
        <!-- Left Column: Profile Section -->
        <div>
            <!-- Profile Photo at Top -->
            <div style="text-align: center; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #eee;">
                <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                    <img class="avatar" id="profile-avatar" src="<?= encode($user['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="<?= encode($user['name']); ?>" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; cursor: pointer;">
                    <div class="avatar-overlay" id="avatar-overlay" style="position: absolute; top: 0; left: 0; width: 150px; height: 150px; border-radius: 50%; background: rgba(0, 0, 0, 0.5); display: none; align-items: center; justify-content: center; cursor: pointer;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12C15.5 10.067 13.933 8.5 12 8.5C10.067 8.5 8.5 10.067 8.5 12C8.5 13.933 10.067 15.5 12 15.5Z" fill="white"/>
                            <path d="M9 2L7.17 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4H16.83L15 2H9ZM12 17C9.24 17 7 14.76 7 12C7 9.24 9.24 7 12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17Z" fill="white"/>
                        </svg>
                    </div>
                </div>
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0.5rem 0;">
                    <h2 style="margin: 0;"><?= encode($user['name']); ?></h2>
                    <button type="button" id="edit-account-info-btn" style="background: none; border: none; cursor: pointer; padding: 0.25rem; display: flex; align-items: center; justify-content: center;" title="Edit Account Information">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #666;">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <p style="color: #666; margin: 0.5rem 0;"><?= encode($user['email']); ?></p>
                
                <?php
                $tier = $user['reward_tier'] ?? 'bronze';
                $points = (float)($user['reward_points'] ?? 0);
                $tierName = get_tier_name($tier);
                $tierColor = get_tier_color($tier);
                ?>
                
                <!-- Reward Points & Tier Display -->
                <div style="margin: 1.5rem 0; padding: 1rem; background: #f9f9f9; border-radius: 8px;">
                    <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.3rem;">Reward Points</div>
                            <div style="font-size: 1.8rem; font-weight: bold; color: #0066cc;"><?= number_format($points, 0); ?></div>
                        </div>
                        <div style="width: 1px; height: 40px; background: #ddd;"></div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.3rem;">Member Tier</div>
                            <span class="tier-badge" style="display: inline-block; padding: 0.5rem 1rem; background: <?= $tierColor; ?>; color: <?= $tier === 'platinum' || $tier === 'silver' ? '#333' : '#fff'; ?>; border-radius: 20px; font-weight: bold; font-size: 1rem;">
                                <?= encode($tierName); ?>
                            </span>
                        </div>
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #666;">
                        Earn 1 point for every RM100 spent
                    </div>
                </div>

                <!-- Daily Check-In Section -->
                <div style="padding: 1.25rem; background: #fff; border: 1px solid #e0e7f1; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.25rem; color: #0e3d73;">Daily Check-In</h3>
                            <p style="margin: 0.25rem 0 0; color: #315c99; font-size: 0.9rem;">Check in once every day to climb the streak ladder.</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.85rem; color: #315c99;">Current streak</div>
                            <div style="font-size: 1.5rem; font-weight: 600; color: #0e3d73;"><?= $streak; ?> day<?= $streak === 1 ? '' : 's'; ?></div>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <?php if ($checkedInToday): ?>
                            <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: #e8f6ff; color: #0f6aa1; border-radius: 999px; padding: 0.5rem 0.9rem; font-weight: 600;">
                                <svg width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.777 12.031l-2.808-2.79-1.06 1.058L7.777 14.14l7.07-7.07-1.06-1.06-6.01 6.02z" fill="#0f6aa1"/>
                                </svg>
                                Checked in today
                            </span>
                            <div style="font-size: 0.9rem; color: #315c99;">
                                Come back tomorrow for <?= $checkInPoints[$nextStreakForReward]; ?> pts.
                            </div>
                        <?php else: ?>
                            <form method="post" action="?module=profile&action=check_in">
                                <button type="submit" class="btn primary" style="padding: 0.6rem 1.5rem; font-weight: 600;">
                                    Check in (+<?= $currentReward; ?> pts)
                                </button>
                            </form>
                            <div style="font-size: 0.9rem; color: #315c99;">
                                Don’t break the streak! Today’s check-in is worth <?= $currentReward; ?> pts.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 0.6rem;">
                        <?php foreach ($checkInPoints as $day => $pointValue): 
                            $isCompleted = $streak >= $day;
                            $isToday = $checkedInToday && $streak === $day;
                            $isUpcoming = !$checkedInToday && $nextStreakForReward === $day;
                            $borderColor = $isToday ? '#0f6aa1' : ($isUpcoming ? '#0e3d73' : ($isCompleted ? '#1e88e5' : '#cfe0ff'));
                            $bgColor = $isToday ? '#e8f6ff' : ($isUpcoming ? '#e1ebff' : '#f7faff');
                            $textColor = $isCompleted ? '#0f6aa1' : '#315c99';
                        ?>
                            <div style="border: 1px solid <?= $borderColor; ?>; border-radius: 8px; padding: 0.6rem; background: <?= $bgColor; ?>; text-align: center;">
                                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: <?= $textColor; ?>;">Day <?= $day; ?></div>
                                <div style="font-size: 1.1rem; font-weight: 600; color: <?= $textColor; ?>;"><?= $pointValue; ?> pts</div>
                                <?php if ($isToday): ?>
                                    <div style="font-size: 0.75rem; color: #0f6aa1;">Today</div>
                                <?php elseif ($isUpcoming): ?>
                                    <div style="font-size: 0.75rem; color: #0e3d73;">Next up</div>
                                <?php elseif ($isCompleted): ?>
                                    <div style="font-size: 0.75rem; color: #0f6aa1;">Done</div>
                                <?php else: ?>
                                    <div style="font-size: 0.75rem; color: #6c8bc2;">Pending</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Saved Addresses Section -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2>Saved Addresses</h2>
                <button type="button" id="add-new-address-btn" class="btn primary">Add New Address</button>
            </div>
        <?php if (empty($savedAddresses)): ?>
            <p style="color: #666;">No saved addresses yet. Click "Add New Address" to save one.</p>
        <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($savedAddresses as $addr): ?>
                    <div class="card" style="padding: 1rem; border: 1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <div>
                                <strong><?= encode($addr['label']); ?></strong>
                                <?php if ($addr['is_default']): ?>
                                    <span style="background: #eaf8ee; color: #1e7e34; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; margin-left: 0.5rem;">Default</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <ul class="detail-list" style="margin: 0; padding: 0;">
                            <li><strong>Name:</strong> <?= encode($addr['name']); ?></li>
                            <li><strong>Phone:</strong> <?= encode($addr['phone']); ?></li>
                            <li><strong>Address:</strong> <?= encode($addr['address']); ?></li>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</section>

<!-- Account Information Modal -->
<div class="modal-overlay" id="account-info-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Account Information</h3>
            <button type="button" class="modal-close" id="close-account-info-modal">&times;</button>
        </div>
        <form method="post" action="?module=profile&action=update" id="profile-form">
            <h4 style="margin-top: 0; margin-bottom: 1rem;">Account Details</h4>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= encode($user['name']); ?>" required>
            <?php err('name'); ?>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= encode($user['phone']); ?>" required>
            <?php err('phone'); ?>

            <label for="address">Address</label>
            <textarea id="address" name="address" required><?= encode($user['address'] ?? ''); ?></textarea>
            <?php err('address'); ?>

            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                <button type="button" class="btn secondary" id="cancel-account-info-btn">Cancel</button>
                <button type="submit" class="btn primary">Save Changes</button>
            </div>
        </form>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #eee;">

        <form method="post" action="?module=profile&action=password" id="password-form">
            <h4 style="margin-top: 0; margin-bottom: 1rem;">Change Password</h4>
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
            <?php err('current_password'); ?>

            <label for="password">New Password</label>
            <input type="password" id="password" name="password" required>
            <?php err('password'); ?>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <?php err('confirm_password'); ?>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Photo Modal -->
<div class="modal-overlay" id="upload-photo-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Upload Profile Photo</h3>
            <button type="button" class="modal-close" id="close-photo-modal">&times;</button>
        </div>
        <form method="post" action="?module=profile&action=photo" enctype="multipart/form-data" id="upload-photo-form">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="position: relative; display: inline-block;">
                    <img id="photo-preview" src="<?= encode($user['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="Preview" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                </div>
            </div>
            <input type="file" id="avatar-input" name="avatar" accept="image/*" required style="display: none;">
            <?php err('avatar'); ?>
            <div style="text-align: center; margin-top: 1rem;">
                <button type="button" id="select-photo-btn" class="btn secondary">Select Photo</button>
            </div>
            <p style="font-size: 0.85rem; color: #666; margin-top: 0.5rem; text-align: center;">Supported formats: JPG, PNG, GIF</p>

            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                <button type="button" class="btn secondary" id="cancel-photo-btn">Cancel</button>
                <button type="submit" class="btn primary">Upload Photo</button>
            </div>
        </form>
    </div>
</div>

<!-- Add New Address Modal -->
<div class="modal-overlay" id="add-address-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Address</h3>
            <button type="button" class="modal-close" id="close-address-modal">&times;</button>
        </div>
        <form id="add-address-form">
            <label for="profile_address_label">Address Label (e.g., Home, Office)</label>
            <input type="text" id="profile_address_label" name="profile_address_label" placeholder="Home" required>
            <?php err('profile_address_label'); ?>

            <label for="profile_address_name">Recipient Name</label>
            <input type="text" id="profile_address_name" name="profile_address_name" required>
            <?php err('profile_address_name'); ?>

            <label for="profile_address_phone">Phone</label>
            <input type="text" id="profile_address_phone" name="profile_address_phone" required>
            <?php err('profile_address_phone'); ?>

            <label for="profile_address_address">Address</label>
            <textarea id="profile_address_address" name="profile_address_address" required></textarea>
            <?php err('profile_address_address'); ?>

            <label style="display: flex; align-items: center; margin-top: 1rem;">
                <input type="checkbox" id="profile_is_default" name="profile_is_default" style="width: auto; margin-right: 0.5rem;">
                Set as default address
            </label>

            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                <button type="button" class="btn secondary" id="cancel-address-btn">Cancel</button>
                <button type="button" class="btn primary" id="save-address-btn">Save Address</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function initProfile() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initProfile, 50);
            return;
        }

        jQuery(function($) {
            var isEditing = false;
            var originalValues = {};

            // Profile Photo Hover Effect
            var $avatarContainer = $('#profile-avatar').parent();
            var $avatarOverlay = $('#avatar-overlay');
            
            $avatarContainer.on('mouseenter', function() {
                $avatarOverlay.fadeIn(200);
            });
            
            $avatarContainer.on('mouseleave', function() {
                $avatarOverlay.fadeOut(200);
            });

            // Open Upload Photo Modal
            $('#profile-avatar, #avatar-overlay').on('click', function() {
                $('#upload-photo-modal').addClass('show');
            });

            // Close Upload Photo Modal
            $('#close-photo-modal, #cancel-photo-btn').on('click', function() {
                $('#upload-photo-modal').removeClass('show');
                $('#avatar-input').val('');
                // Reset preview to original
                var originalSrc = $('#profile-avatar').attr('src');
                $('#photo-preview').attr('src', originalSrc);
            });

            // Close photo modal when clicking outside
            $('#upload-photo-modal').on('click', function(e) {
                if ($(e.target).is('#upload-photo-modal')) {
                    $(this).removeClass('show');
                    $('#avatar-input').val('');
                    var originalSrc = $('#profile-avatar').attr('src');
                    $('#photo-preview').attr('src', originalSrc);
                }
            });

            // Trigger file input when button is clicked
            $('#select-photo-btn').on('click', function() {
                $('#avatar-input').click();
            });

            // Photo Preview
            $('#avatar-input').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photo-preview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Account Information Modal
            $('#edit-account-info-btn').on('click', function() {
                $('#account-info-modal').addClass('show');
            });

            $('#close-account-info-modal, #cancel-account-info-btn').on('click', function() {
                $('#account-info-modal').removeClass('show');
                $('#password-form')[0].reset();
            });

            // Close account info modal when clicking outside
            $('#account-info-modal').on('click', function(e) {
                if ($(e.target).is('#account-info-modal')) {
                    $(this).removeClass('show');
                    $('#password-form')[0].reset();
                }
            });

            // Add New Address Modal
            $('#add-new-address-btn').on('click', function() {
                $('#add-address-modal').addClass('show');
            });

            $('#close-address-modal, #cancel-address-btn').on('click', function() {
                $('#add-address-modal').removeClass('show');
                $('#add-address-form')[0].reset();
            });

            // Close address modal when clicking outside
            $('#add-address-modal').on('click', function(e) {
                if ($(e.target).is('#add-address-modal')) {
                    $(this).removeClass('show');
                    $('#add-address-form')[0].reset();
                }
            });

            // Save address from profile
            $('#save-address-btn').on('click', function() {
                var label = $('#profile_address_label').val().trim();
                var name = $('#profile_address_name').val().trim();
                var phone = $('#profile_address_phone').val().trim();
                var address = $('#profile_address_address').val().trim();

                if (!label || !name || !phone || !address) {
                    alert('Please fill in all fields.');
                    return;
                }

                // Create form and submit
                var form = $('<form>', {
                    method: 'POST',
                    action: '?module=profile&action=save_address',
                    style: 'display: none;'
                });
                form.append($('<input>', {type: 'hidden', name: 'label', value: label}));
                form.append($('<input>', {type: 'hidden', name: 'name', value: name}));
                form.append($('<input>', {type: 'hidden', name: 'phone', value: phone}));
                form.append($('<input>', {type: 'hidden', name: 'address', value: address}));
                if ($('#profile_is_default').is(':checked')) {
                    form.append($('<input>', {type: 'hidden', name: 'is_default', value: '1'}));
                }
                $('body').append(form);
                form.submit();
            });
        });
    }

    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProfile);
    } else {
        initProfile();
    }
})();
</script>
