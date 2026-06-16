<?php

it('returns the framework health endpoint', function () {
    $this->get('/up')->assertOk();
});
