<?php

test('halaman utama mengalihkan pengunjung', function () {
    $this->get('/')->assertStatus(302);
});