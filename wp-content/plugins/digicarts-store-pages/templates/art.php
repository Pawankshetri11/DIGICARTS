<?php defined('ABSPATH') || exit; ?>
<div class="dsp-art dsp-art-<?php echo esc_attr($type); ?>" aria-hidden="true">
<?php if ($type==='web') : ?>
    <div class="dsp-browser"><div class="dsp-browser-bar"><i></i><i></i><i></i><span>your next website</span></div><div class="dsp-browser-page"><div class="dsp-mini-nav">STUDIO<span>Work &nbsp; About &nbsp; ↗</span></div><strong>Less ordinary.<br>More you.</strong><div class="dsp-mini-layout"><span></span><b>Ideas<br>with<br>impact.</b></div></div></div><span class="dsp-art-label">Made to make yours ↗</span>
<?php elseif ($type==='design') : ?>
    <div class="dsp-design-sheet"><small>THE CREATIVE EDIT</small><strong>Play with<br>possibility.</strong><div class="dsp-design-shapes"><i>✳</i><b></b><span></span></div><div class="dsp-swatches"><i></i><i></i><i></i><i></i></div></div><span class="dsp-art-label">A little creative freedom.</span>
<?php elseif ($type==='book') : ?>
    <div class="dsp-book"><small>A FRESH PERSPECTIVE</small><strong>Good ideas.<br>New<br>beginnings.</strong><span class="dsp-book-sun"></span><em>Something worth exploring ↗</em></div><span class="dsp-art-label">Turn a page. Start something.</span>
<?php else : ?>
    <div class="dsp-planner"><small>YOUR EVERYDAY EDIT</small><strong>A little more<br>headspace.</strong><div><span>01</span> Make room for ideas</div><div><span>02</span> Focus on what matters</div><div><span>03</span> Make something good</div><footer><i></i><i></i><i></i><i></i><i></i></footer></div><span class="dsp-art-label">A plan. Not pressure.</span>
<?php endif; ?>
</div>
