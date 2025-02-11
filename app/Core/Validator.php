<?php

namespace App\Core;

class Validator
{
    private $errors = [];

    // Validate string
    public function validateString($str, $errorname)
    {
        if (empty(trim($str))) {
            $this->errors["$errorname"] = " $errorname est requis.";
        }
    }

    // Validate Email
    public function validateEmail($email)
    {
        if (empty($email)) {
            $this->errors['email'] = "L'adresse email est requise.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Veuillez entrer une adresse email valide.";
        }
    }
    // Validate Password 
    public function validatePassword($password)
    {
        if (empty($password)) {
            $this->errors['password'] = "Le mot de passe est requis.";
        } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password)) {
            $this->errors['password'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.";
        }
    }

    // Validate Confirm Password
    public function validateConfirmPassword($password, $confirm_password)
    {
        if (empty($confirm_password)) {
            $this->errors['confirm_password'] = "Veuillez confirmer votre mot de passe.";
        } elseif ($password !== $confirm_password) {
            $this->errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
        }
    }

    // Get Validation Errors
    public function getErrors()
    {
        return $this->errors;
    }

    // Check if Validation Passed
    public function isValid()
    {
        return empty($this->errors);
    }
}