<?php

test('la page Radio Francine expose la bannière PWA et le bouton mute', function () {
    $file = realpath(__DIR__.'/../../resources/js/pages/RadioFrancine.vue');

    expect($file)->not->toBeFalse();

    $contents = file_get_contents($file);

    expect($contents)->toContain('data-testid="pwa-install-banner"');
    expect($contents)->toContain('data-testid="mute-toggle"');
});
