<?php

use App\Support\TextUtils;

test('it generates acronym from words', function () {
    expect(TextUtils::acronym('My Test Project'))->toBe('MTP');
});

test('it limits acronym length', function () {
    expect(TextUtils::acronym('one two three four five six'))->toBe('OTTFF');
});

test('it supports unicode words', function () {
    expect(TextUtils::acronym('мій тестовий проєкт'))->toBe('МТП');
});
