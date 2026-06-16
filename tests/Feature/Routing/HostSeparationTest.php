<?php

function hostFor(string $configKey): string
{
    return (string) parse_url((string) config($configKey), PHP_URL_HOST);
}

function urlFor(string $configKey): string
{
    return (string) config($configKey);
}

it('serves the public home on the public host', function () {
    $this->withServerVariables(['HTTP_HOST' => hostFor('app.public_url')])
        ->get(urlFor('app.public_url'))
        ->assertOk()
        ->assertSeeText('Participá en sorteos transparentes')
        ->assertDontSeeText('Administración de sorteos');
});

it('serves the admin home on the admin host', function () {
    $this->withServerVariables(['HTTP_HOST' => hostFor('app.admin_url')])
        ->get(urlFor('app.admin_url'))
        ->assertOk()
        ->assertSeeText('Administración de sorteos')
        ->assertDontSeeText('Participá en sorteos transparentes');
});

it('rejects unknown hosts for the root route', function () {
    $this->withServerVariables(['HTTP_HOST' => 'raffles.test'])
        ->get('http://raffles.test/')
        ->assertNotFound();
});
