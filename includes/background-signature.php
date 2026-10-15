<?php // Decorative vector lettering: no font loading, canvas or animation loop. ?>
<div class="xv-ambient" aria-hidden="true">
<svg class="xv-signature" viewBox="0 0 640 640" focusable="false">
<defs>
    <!-- The S is drawn in a 400-unit square; its 400-unit cap height becomes
         386.4 units, exactly 69% of the X's 560-unit cap height. -->
    <path id="xv-letter-s" d="M344 57C311 20 264 0 205 0C119 0 61 48 61 114C61 176 108 205 190 224L229 233C278 245 300 261 300 286C300 319 266 340 216 340C157 340 111 315 77 274L40 322C81 372 141 400 214 400C302 400 360 352 360 282C360 219 317 188 233 168L193 159C146 148 122 133 122 108C122 78 153 59 201 59C247 59 283 76 308 105Z"/>
    <mask id="xv-letter-cut" maskUnits="userSpaceOnUse" x="0" y="0" width="640" height="640">
        <rect width="640" height="640" fill="white"/>
        <svg x="126.8" y="126.8" width="386.4" height="386.4" viewBox="0 0 400 400" overflow="visible"><use href="#xv-letter-s" fill="black" stroke="black" stroke-width="16" stroke-linejoin="round"/></svg>
    </mask>
</defs>
<!-- A narrow, chamfered X; the small cut around S separates the two letters. -->
<path class="xv-signature-x" mask="url(#xv-letter-cut)" d="M103 40H178L320 264L462 40H537L357 320L537 600H462L320 376L178 600H103L283 320Z"/>
<svg x="126.8" y="126.8" width="386.4" height="386.4" viewBox="0 0 400 400" overflow="visible"><use class="xv-signature-s" href="#xv-letter-s"/></svg>
</svg>
</div>
