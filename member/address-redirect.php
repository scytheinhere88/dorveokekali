<?php
/**
 * REDIRECT: address.php -> address-book.php
 * Old address page, redirect to new Address Book
 */
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

// Redirect to new address book page
redirect('/member/address-book.php');
