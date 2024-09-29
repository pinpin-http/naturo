<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; // N'oublie pas d'importer cette classe
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    use VerifiesEmails;

    public function __construct()
    {
      
    }
}

/* bilan vital = questionnaire */