<?php

function adminRegistrationRuntimeSource(): string
{
    $paths = [
        'app/Http/Controllers/Admin/RaffleController.php',
        'app/Http/Resources/Admin/RaffleRegistrationSnapshot.php',
        'routes/admin.php',
        'resources/js/app.js',
        'resources/js/admin/raffle-registrations/RaffleRegistrations.vue',
        'resources/js/admin/raffle-registrations/snapshot.js',
        'vite.config.js',
    ];

    return collect($paths)->map(function (string $path): string {
        $absolutePath = base_path($path);

        expect($absolutePath)->toBeFile();

        return file_get_contents($absolutePath);
    })->implode("\n");
}

function realtimeCandidateDocument(string $path): string
{
    $absolutePath = base_path($path);

    expect($absolutePath)->toBeFile();

    return file_get_contents($absolutePath);
}

it('No runtime transport is introduced', function () {
    $runtime = adminRegistrationRuntimeSource();

    expect($runtime)
        ->not->toMatch('/\b(?:EventSource|WebSocket|BroadcastChannel|ShouldBroadcast(?:Now)?|Listener|Channel)\b/')
        ->not->toMatch('/(?:Broadcast::|broadcast\s*\(|dispatch\s*\(|Echo\.|set(?:Interval|Timeout)\s*\()/')
        ->not->toMatch('/addEventListener\s*\(\s*[\'\"](?:storage|message)[\'\"]/');
});

it('Labels are not executable contracts', function () {
    $candidateMap = realtimeCandidateDocument('openspec/specs/realtime-update-candidate-map/spec.md');
    preg_match_all('/`([A-Z][A-Za-z]+)` \(not implemented\)/', $candidateMap, $matches);

    expect($matches[1])
        ->toContain('RegistrationStatusChanged')
        ->and(adminRegistrationRuntimeSource())
        ->not->toMatch('/\b(?:'.implode('|', array_map('preg_quote', $matches[1])).')\b/');
});

it('Candidate classification remains documentation-only', function () {
    $delta = realtimeCandidateDocument(
        'openspec/specs/realtime-update-candidate-map/spec.md'
    );

    expect($delta)
        ->toContain('admin registration list SHALL remain a **Candidate**')
        ->toContain('runtime realtime behavior SHALL require a separate future specification')
        ->and(adminRegistrationRuntimeSource())
        ->not->toContain('Candidate');
});
