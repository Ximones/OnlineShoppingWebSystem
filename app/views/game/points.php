<?php
$title = 'Points';
$points = (float) ($points ?? 0.0);
$playCost = (float) ($playCost ?? 50.0);
$checkInPoints = $checkInPoints ?? [
    1 => 1,
    2 => 5,
    3 => 10,
    4 => 15,
    5 => 20,
    6 => 25,
    7 => 100,
];
$streak = $streak ?? 0;
$checkedInToday = $checkedInToday ?? false;
$nextStreakForReward = $nextStreakForReward ?? 1;
$currentReward = $currentReward ?? 1;
?>

<section class="panel" style="max-width: 960px; margin: 0 auto;">
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <header style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin: 0 0 0.25rem;">Points</h2>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.9rem;">
                    Daily check-in and scratch card games to earn reward points.
                </p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.85rem; color: var(--color-text-muted);">Your Points</div>
                <div id="sc-points-display" style="font-size: 1.6rem; font-weight: 700; color: #0066cc;">
                    <?= number_format($points, 0); ?>
                </div>
            </div>
        </header>

        <!-- Daily Check-In Section -->
        <div style="padding: 1.25rem; background: #fff; border: 1px solid #e0e7f1; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; color: #0e3d73;">Daily Check-In</h3>
                    <p style="margin: 0.25rem 0 0; color: #315c99; font-size: 0.9rem;">Check in once every day to climb the streak ladder.</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.85rem; color: #315c99;">Current streak</div>
                    <div style="font-size: 1.5rem; font-weight: 600; color: #0e3d73;"><?= $streak; ?> day<?= $streak === 1 ? '' : 's'; ?></div>
                </div>
            </div>

            <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
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
                    <form method="post" action="?module=game&action=check_in">
                        <button type="submit" class="btn primary" style="padding: 0.6rem 1.5rem; font-weight: 600;">
                            Check in (+<?= $currentReward; ?> pts)
                        </button>
                    </form>
                    <div style="font-size: 0.9rem; color: #315c99;">
                        Don't break the streak! Today's check-in is worth <?= $currentReward; ?> pts.
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 0.6rem;">
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

        <!-- Scratch Card Game Container -->
        <div style="padding: 1.25rem; background: #fff; border: 1px solid #e0e7f1; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1.25rem; color: #0e3d73;">Scratch Card Game</h3>
                <p style="margin: 0.25rem 0 0; color: #315c99; font-size: 0.9rem;">Spend your reward points to reveal a prize. Each card costs <strong><?= number_format($playCost, 0); ?> pts</strong>.</p>
            </div>
            
            <!-- Prize list - full width at top -->
            <div style="width: 100%; border-radius: 12px; padding: 1rem; background: rgba(255,255,255,0.95); box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 0.75rem 0; font-size: 1rem; color: #0e3d73;">Prize List</h3>
            <div style="display: flex; gap: 0.75rem; flex-wrap: nowrap; width: 100%;">
                        <!-- No prize -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: rgba(255,255,255,0.95); border: 2px solid #e0e0e0;"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">No prize</div>
                            <div style="font-weight: 600; color: #666; font-size: 0.8rem;">0 pts</div>
                        </div>
                        <!-- Common -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #84fab0, #8fd3f4);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Common</div>
                            <div style="font-weight: 600; color: #0d4f1c; font-size: 0.8rem;">+10 pts</div>
                        </div>
                        <!-- Uncommon -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #4facfe, #00f2fe);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Uncommon</div>
                            <div style="font-weight: 600; color: #0a2540; font-size: 0.8rem;">+30 pts</div>
                        </div>
                        <!-- Rare -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #a18cd1, #fbc2eb);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Rare</div>
                            <div style="font-weight: 600; color: #280b3a; font-size: 0.8rem;">+80 pts</div>
                        </div>
                        <!-- Epic -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #f6d365, #fda085);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Epic</div>
                            <div style="font-weight: 600; color: #3b2200; font-size: 0.8rem;">+150 pts</div>
                        </div>
                        <!-- Jackpot -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #ff6b35, #f7931e, #ffcc00);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Jackpot</div>
                            <div style="font-weight: 600; color: #3b2200; font-size: 0.8rem;">+500 pts</div>
                        </div>
                        <!-- Mega Jackpot -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #e8e8e8, #c0c0c0, #a8a8a8);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Mega</div>
                            <div style="font-weight: 600; color: #1a1a1a; font-size: 0.8rem;">+1000 pts</div>
                        </div>
                        <!-- Ultimate Jackpot -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.75rem; border-radius: 8px; background: #f9f9f9; flex: 1;">
                            <div style="width: 50px; height: 50px; border-radius: 6px; background: linear-gradient(135deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3);"></div>
                            <div style="font-weight: 500; color: #333; font-size: 0.85rem; text-align: center;">Ultimate</div>
                            <div style="font-weight: 600; color: #1a1a1a; font-size: 0.8rem;">+5000 pts</div>
                        </div>
                    </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(260px, 1fr); gap: 1.5rem; align-items: stretch;">
            <!-- Scratch card -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                <div id="sc-card" style="position: relative; width: 100%; max-width: 420px; aspect-ratio: 3 / 2; border-radius: 18px; padding: 1.25rem; background: radial-gradient(circle at top, #314165, #0e1524); box-shadow: 0 12px 24px rgba(0,0,0,0.35); cursor: pointer;">
                    <div style="position: absolute; inset: 0.6rem; border-radius: 14px; background: linear-gradient(145deg, #f7f7f7, #e0e0e0); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <!-- Hidden prize -->
                        <div id="sc-prize"
                             style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.4rem; font-weight: 700; color: #0e3d73; font-size: 1.3rem; opacity: 1; transform: scale(1); transition: transform 0.25s ease; padding: 1rem; border-radius: 14px; background: transparent;">
                            <span id="sc-prize-label">Tap to Scratch</span>
                            <span id="sc-prize-sub" style="font-size: 0.9rem; font-weight: 500; color: #315c99;"></span>
                        </div>

                        <!-- Gray scratch layer -->
                        <canvas id="sc-layer"
                                style="position: absolute; inset: 0; width: 100%; height: 100%; border-radius: 14px; pointer-events: auto; z-index: 10;"></canvas>
                    </div>

                    <div style="position: absolute; left: 1.3rem; top: 1rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.14em; color: rgba(255,255,255,0.7);">
                        Scratch Area
                    </div>
                    <div id="sc-status-chip" style="position: absolute; right: 1.3rem; top: 1rem; font-size: 0.8rem; padding: 0.25rem 0.7rem; border-radius: 999px; background: rgba(3, 187, 133, 0.16); color: #8df5c7; border: 1px solid rgba(173, 255, 222, 0.6);">
                        Ready
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <aside style="background: #0b1729; border-radius: 12px; padding: 1.25rem 1.2rem; color: #f9fbff; box-shadow: 0 10px 20px rgba(0,0,0,0.35); display: flex; flex-direction: column; gap: 0.9rem;">
                <div>
                    <div style="font-size: 0.85rem; color: #9fb3d9; margin-bottom: 0.4rem; font-weight: 500;">How it works</div>
                    <ul style="margin: 0; padding-left: 1rem; font-size: 0.8rem; color: #9fb3d9; line-height: 1.4;">
                        <li>Costs <strong><?= number_format($playCost, 0); ?> pts</strong> per scratch</li>
                        <li>Scratch 40% to reveal prize</li>
                        <li>Claim to add points back</li>
                    </ul>
                </div>

                <button id="sc-play-btn" class="btn primary" style="width: 100%;">
                    Scratch (<?= number_format($playCost, 0); ?> pts)
                </button>

                <button id="sc-claim-btn" class="btn primary" style="width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>
                    Claim Prize
                </button>

                <p style="margin: 0; font-size: 0.78rem; color: #8ba2d0; line-height: 1.5;">
                    Points are deducted immediately when you start scratching. Prizes are credited back to your reward points balance.
                </p>
            </aside>
        </div>
        </div>
    </div>
</section>

<script>
(function () {
    function initScratchCard() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScratchCard, 50);
            return;
        }

        jQuery(function ($) {
            var $card = $('#sc-card');
            var $canvas = $('#sc-layer');
            var $pointsDisplay = $('#sc-points-display');
            var $statusChip = $('#sc-status-chip');
            var $playBtn = $('#sc-play-btn');
            var $claimBtn = $('#sc-claim-btn');
            var $prize = $('#sc-prize');
            var $prizeLabel = $('#sc-prize-label');
            var $prizeSub = $('#sc-prize-sub');

            var isScratching = false;
            var hasActiveCard = false; // backend prize has been loaded and user can scratch
            var scratchStrokes = 0;
            var hasRevealed = false;
            var lastPrize = null;

            var canvas = $canvas.get(0);
            var ctx = canvas.getContext('2d');

            function resizeCanvas() {
                var rect = $canvas[0].getBoundingClientRect();
                // Use device pixel ratio for crisp rendering
                var dpr = window.devicePixelRatio || 1;
                var visualWidth = rect.width;
                var visualHeight = rect.height;
                canvas.width = visualWidth * dpr;
                canvas.height = visualHeight * dpr;
                canvas.style.width = visualWidth + 'px';
                canvas.style.height = visualHeight + 'px';
                ctx.scale(dpr, dpr);
                // Redraw the scratch layer using visual coordinates (context is scaled)
                // Fill with solid gray scratch pattern - fully opaque
                var scratchPattern = ctx.createPattern(createScratchPattern(), 'repeat');
                if (scratchPattern) {
                    ctx.fillStyle = scratchPattern;
                } else {
                    // Fallback: solid gray with diagonal stripes
                    ctx.fillStyle = '#b8bcc4';
                }
                ctx.fillRect(0, 0, visualWidth, visualHeight);
                // Add diagonal stripe pattern on top
                ctx.strokeStyle = '#cfd3da';
                ctx.lineWidth = 1;
                var stripeSpacing = 12;
                for (var i = -visualHeight; i < visualWidth + visualHeight; i += stripeSpacing) {
                    ctx.beginPath();
                    ctx.moveTo(i, 0);
                    ctx.lineTo(i + visualHeight, visualHeight);
                    ctx.stroke();
                }
            }
            
            function createScratchPattern() {
                // Create a temporary canvas for the scratch pattern
                var patternCanvas = document.createElement('canvas');
                patternCanvas.width = 12;
                patternCanvas.height = 12;
                var patternCtx = patternCanvas.getContext('2d');
                // Base gray
                patternCtx.fillStyle = '#b8bcc4';
                patternCtx.fillRect(0, 0, 12, 12);
                // Diagonal stripe
                patternCtx.strokeStyle = '#cfd3da';
                patternCtx.lineWidth = 1;
                patternCtx.beginPath();
                patternCtx.moveTo(0, 0);
                patternCtx.lineTo(12, 12);
                patternCtx.stroke();
                return patternCanvas;
            }

            function setStatus(text, variant) {
                $statusChip.text(text);
                var base = 'position:absolute;right:1.3rem;top:1rem;font-size:0.8rem;padding:0.25rem 0.7rem;border-radius:999px;border:1px solid;';
                if (variant === 'busy') {
                    $statusChip.attr('style', base + 'background:rgba(122,175,255,0.16);color:#c3d8ff;border-color:rgba(165,196,255,0.8);');
                } else if (variant === 'error') {
                    $statusChip.attr('style', base + 'background:rgba(255,82,82,0.18);color:#ffc6c6;border-color:rgba(255,180,180,0.9);');
                } else if (variant === 'success') {
                    $statusChip.attr('style', base + 'background:rgba(3,187,133,0.16);color:#8df5c7;border-color:rgba(173,255,222,0.6);');
                } else {
                    $statusChip.attr('style', base + 'background:rgba(3,187,133,0.16);color:#8df5c7;border-color:rgba(173,255,222,0.6);');
                }
            }

            function clearCardSurface() {
                resizeCanvas();
                // Neutral background before a prize is loaded
                $prize.css({
                    background: 'transparent',
                    color: '#0e3d73',
                    transform: 'scale(1)'
                });
                $prizeLabel.text('Tap to Scratch');
                $prizeSub.text('');
                // Re-enable scratch button when clearing
                $playBtn.prop('disabled', false);
                $playBtn.css({
                    'opacity': '1',
                    'cursor': 'pointer',
                    'background': '',
                    'color': ''
                });
                // Disable claim button and style it
                $claimBtn.prop('disabled', true);
                $claimBtn.css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'background': '#e5e5ea',
                    'color': '#8e8e93'
                });
            }

            function getPrizeColor(prize) {
                var label = (prize && prize.label) ? prize.label.toLowerCase() : '';
                
                if (label.indexOf('ultimate jackpot') !== -1) {
                    return 'linear-gradient(135deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3)';
                } else if (label.indexOf('mega jackpot') !== -1) {
                    return 'linear-gradient(135deg, #e8e8e8, #c0c0c0, #a8a8a8)';
                } else if (label.indexOf('jackpot') !== -1) {
                    return 'linear-gradient(135deg, #ff6b35, #f7931e, #ffcc00)';
                } else if (label.indexOf('epic') !== -1) {
                    return 'linear-gradient(135deg, #f6d365, #fda085)';
                } else if (label.indexOf('rare') !== -1) {
                    return 'linear-gradient(135deg, #a18cd1, #fbc2eb)';
                } else if (label.indexOf('uncommon') !== -1) {
                    return 'linear-gradient(135deg, #4facfe, #00f2fe)';
                } else if (label.indexOf('common') !== -1) {
                    return 'linear-gradient(135deg, #84fab0, #8fd3f4)';
                } else {
                    return 'rgba(255,255,255,0.95)';
                }
            }

            function applyPrizeTierBackground(prize) {
                var bgColor = getPrizeColor(prize);
                var label = (prize && prize.label) ? prize.label.toLowerCase() : '';
                
                // Apply background color based on prize label
                if (label.indexOf('ultimate jackpot') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#ffffff',
                        textShadow: '0 0 8px rgba(0,0,0,0.8)'
                    });
                } else if (label.indexOf('mega jackpot') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#1a1a1a',
                        textShadow: '0 0 4px rgba(255,255,255,0.5)'
                    });
                } else if (label.indexOf('jackpot') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#3b2200',
                        textShadow: '0 0 4px rgba(255,255,255,0.6)'
                    });
                } else if (label.indexOf('epic') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#3b2200'
                    });
                } else if (label.indexOf('rare') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#280b3a'
                    });
                } else if (label.indexOf('uncommon') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#0a2540'
                    });
                } else if (label.indexOf('common') !== -1) {
                    $prize.css({
                        background: bgColor,
                        color: '#0d4f1c'
                    });
                } else {
                    $prize.css({
                        background: bgColor,
                        color: '#0e3d73'
                    });
                }
            }

            function revealPrize(prize) {
                applyPrizeTierBackground(prize);
                $prizeLabel.text(prize.label);
                if (prize.type === 'points' && prize.points > 0) {
                    $prizeSub.text('+' + prize.points + ' pts');
                } else {
                    $prizeSub.text('Better luck next time!');
                }
                // Small bump animation when fully revealed
                $prize.css({transform: 'scale(1.02)'});
                setTimeout(function () {
                    $prize.css({transform: 'scale(1)'});
                }, 200);
                // Enable claim button after reveal and restore styling
                $claimBtn.prop('disabled', false);
                $claimBtn.css({
                    'opacity': '1',
                    'cursor': 'pointer',
                    'background': '',
                    'color': ''
                });
            }

            function getScratchedPercentage() {
                // Sample pixels to estimate how much of the canvas is scratched
                var rect = canvas.getBoundingClientRect();
                var visualWidth = rect.width;
                var visualHeight = rect.height;
                var sampleStep = Math.max(5, Math.floor(Math.min(visualWidth, visualHeight) / 50)); // Sample every Nth pixel
                var totalSamples = 0;
                var scratchedSamples = 0;
                
                // Get image data (need to account for device pixel ratio)
                var dpr = window.devicePixelRatio || 1;
                var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                var data = imageData.data;
                
                // Sample pixels
                for (var y = 0; y < visualHeight; y += sampleStep) {
                    for (var x = 0; x < visualWidth; x += sampleStep) {
                        totalSamples++;
                        // Convert visual coordinates to actual canvas coordinates
                        var canvasX = Math.floor(x * dpr);
                        var canvasY = Math.floor(y * dpr);
                        var idx = (canvasY * canvas.width + canvasX) * 4;
                        // Check if pixel is transparent (alpha < 128 means scratched)
                        if (idx < data.length && data[idx + 3] < 128) {
                            scratchedSamples++;
                        }
                    }
                }
                
                return totalSamples > 0 ? (scratchedSamples / totalSamples) * 100 : 0;
            }

            function scratchAt(x, y) {
                if (!hasActiveCard || !lastPrize) return;

                // Get visual dimensions (context is scaled by dpr)
                var rect = canvas.getBoundingClientRect();
                var visualWidth = rect.width;
                var visualHeight = rect.height;

                // Clamp coordinates to visual canvas bounds
                x = Math.max(0, Math.min(visualWidth, x));
                y = Math.max(0, Math.min(visualHeight, y));

                ctx.globalCompositeOperation = 'destination-out';
                // Larger radius for easier scratching
                var radius = Math.max(visualWidth, visualHeight) * 0.12;
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fill();
                ctx.globalCompositeOperation = 'source-over';

                scratchStrokes++;
                
                // Check scratched percentage and update status
                var scratchedPercent = getScratchedPercentage();
                
                if (scratchStrokes === 10) {
                    setStatus('Keep scratching…', 'busy');
                } else if (scratchStrokes === 30) {
                    setStatus('Almost there!', 'busy');
                } else if (scratchStrokes === 55) {
                    setStatus('Scratched!', 'success');
                }
                
                // Update status with percentage
                if (scratchedPercent >= 20 && scratchedPercent < 40) {
                    setStatus(Math.round(scratchedPercent) + '% scratched', 'busy');
                } else if (scratchedPercent >= 40) {
                    setStatus('Scratched!', 'success');
                }

                // Reveal underlying prize when 40% of the card is scratched
                if (!hasRevealed && scratchedPercent >= 40) {
                    revealPrize(lastPrize);
                    hasRevealed = true;
                    // Scratch button stays grayed until Claim Prize is clicked
                }
            }

            function handlePlay() {
                if (hasActiveCard) {
                    // already have a card loaded; let user finish scratching
                    return;
                }

                setStatus('Using points…', 'busy');
                $playBtn.prop('disabled', true).text('Scratching…');

                fetch('?module=game&action=play_scratch', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            return response.json().then(function (data) {
                                throw data;
                            });
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data.success) {
                            throw data;
                        }

                        var balance = data.balance !== undefined ? data.balance : null;
                        if (balance !== null) {
                            $pointsDisplay.text(new Intl.NumberFormat().format(Math.floor(balance)));
                        }

                        scratchStrokes = 0;
                        hasActiveCard = true;
                        hasRevealed = false;
                        lastPrize = data.prize;
                        
                        // Disable and gray out scratch button when starting new scratch
                        $playBtn.prop('disabled', true);
                        $playBtn.css({
                            'opacity': '0.5',
                            'cursor': 'not-allowed',
                            'background': '#e5e5ea',
                            'color': '#8e8e93'
                        });
                        $playBtn.text('Scratch (<?= number_format($playCost, 0); ?> pts)');
                        
                        // Disable claim button when starting new scratch and style it
                        $claimBtn.prop('disabled', true);
                        $claimBtn.css({
                            'opacity': '0.5',
                            'cursor': 'not-allowed',
                            'background': '#e5e5ea',
                            'color': '#8e8e93'
                        });
                        
                        // Apply colored background and prize text immediately so it shows through as user scratches
                        applyPrizeTierBackground(lastPrize);
                        $prizeLabel.text(lastPrize.label);
                        if (lastPrize.type === 'points' && lastPrize.points > 0) {
                            $prizeSub.text('+' + lastPrize.points + ' pts');
                        } else {
                            $prizeSub.text('Better luck next time!');
                        }
                        
                        // Redraw the scratch layer on top to cover the prize
                        resizeCanvas();
                        
                        setStatus('Scratch with your mouse', 'busy');
                    })
                    .catch(function (err) {
                        var message = err && err.message ? err.message : 'Unable to play right now.';
                        setStatus('Not enough points', 'error');
                        // Re-enable scratch button on error
                        $playBtn.prop('disabled', false);
                        $playBtn.css({
                            'opacity': '1',
                            'cursor': 'pointer',
                            'background': '',
                            'color': ''
                        });
                        $playBtn.text('Scratch (<?= number_format($playCost, 0); ?> pts)');
                    });
            }

            $playBtn.on('click', function () {
                handlePlay();
            });

            $claimBtn.on('click', function () {
                if (!lastPrize) return;
                
                var pointsWon = 0;
                if (lastPrize.type === 'points' && lastPrize.points > 0) {
                    pointsWon = lastPrize.points;
                }
                
                // Claim the prize - add points back
                var formData = new FormData();
                formData.append('points_won', pointsWon);
                
                fetch('?module=game&action=claim_scratch_prize', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (data.success && data.balance !== undefined) {
                            $pointsDisplay.text(new Intl.NumberFormat().format(Math.floor(data.balance)));
                        }
                        // Clear the card after claiming
                        clearCardSurface();
                        setStatus('Ready', 'idle');
                        hasActiveCard = false;
                        scratchStrokes = 0;
                        hasRevealed = false;
                        lastPrize = null;
                    })
                    .catch(function () {
                        // On error, still clear the card
                        clearCardSurface();
                        setStatus('Ready', 'idle');
                        hasActiveCard = false;
                        scratchStrokes = 0;
                        hasRevealed = false;
                        lastPrize = null;
                    });
            });

            // Scratch interactions - make entire card scratchable
            function getPos(evt) {
                var rect = canvas.getBoundingClientRect();
                var clientX = evt.touches ? evt.touches[0].clientX : evt.clientX;
                var clientY = evt.touches ? evt.touches[0].clientY : evt.clientY;
                // Calculate position relative to canvas visual size
                // Context is already scaled by dpr, so use visual coordinates
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            // Make the entire card container scratchable, not just the canvas
            $card.on('mousedown touchstart', function (evt) {
                if (!hasActiveCard) return;
                evt.preventDefault();
                evt.stopPropagation();
                isScratching = true;
                var pos = getPos(evt.originalEvent);
                scratchAt(pos.x, pos.y);
            });

            $(document).on('mousemove touchmove', function (evt) {
                if (!isScratching || !hasActiveCard) return;
                evt.preventDefault();
                var pos = getPos(evt.originalEvent);
                scratchAt(pos.x, pos.y);
            });

            $(document).on('mouseup touchend touchcancel', function (evt) {
                if (isScratching) {
                    evt.preventDefault();
                }
                isScratching = false;
            });

            // Initial canvas setup
            resizeCanvas();
            $(window).on('resize', resizeCanvas);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScratchCard);
    } else {
        initScratchCard();
    }
})();
</script>

