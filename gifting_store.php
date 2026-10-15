<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/header.php');
?>

<style>
    .gift-container { max-width: 800px; margin: 80px auto; padding: 30px; text-align: center; font-family: sans-serif; color: #fff; }
    .gift-card { background: #121212; border: 1px solid #ff4747; border-radius: 12px; padding: 40px; box-shadow: 0 0 20px rgba(255, 71, 71, 0.2); }
    .gift-icon { font-size: 60px; margin-bottom: 20px; animation: pulse 2s infinite; }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }
    .gift-card h1 { color: #fff; margin-bottom: 10px; font-size: 32px; letter-spacing: 1px; }
    .gift-card p { color: #bbb; font-size: 16px; margin-bottom: 30px; line-height: 1.6; }
    .coupon-reveal-box { background: #1c1c1c; border: 1px dashed #444; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
    .coupon-code-badge { font-size: 24px; font-weight: bold; color: #ff4747; background: #0a0a0a; padding: 10px 20px; border-radius: 6px; display: inline-block; letter-spacing: 2px; border: 1px solid #333; margin-top: 10px; }
    .claim-btn { display: inline-block; padding: 14px 35px; background: #ff4747; color: #fff; font-weight: bold; font-size: 16px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; transition: 0.3s; }
    .claim-btn:hover { background: #e03e3e; box-shadow: 0 0 15px rgba(255, 71, 71, 0.4); }
</style>

<div class="gift-container">
    <div class="gift-card">
        <div class="gift-icon">🎁</div>
        <h1>VOLT GIFTING MATRIX</h1>
        <p>Unlock premium, limited-edition dynamic hardware updates completely for free. We are deploying a free mystery box directly into your checkout stream.</p>
        
        <div class="coupon-reveal-box">
            <span style="color: #bbb; font-size: 14px; text-transform: uppercase;">Your Secret Access Code:</span><br>
            <span class="coupon-code-badge">FREEVOLT</span>
        </div>

        <!-- This form submits straight into your existing coupon controller -->
        <form action="cart_action.php?action=coupon" method="POST">
            <input type="hidden" name="coupon_code" value="FREEVOLT">
            <button type="submit" class="claim-btn">CLAIM FREE GIFT ⚡</button>
        </form>
    </div>
</div>

</body>
</html>