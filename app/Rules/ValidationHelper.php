<?php

namespace App\Rules;

use App\Helpers\Helper;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ValidationHelper
{
    public static function individualProfileValidator(): array
    {
        return [
            'bank_account_number' => self::bankAccountNumberValidator(),
            'bank_account_name' => self::bankAccountNameValidator(),
            'bank_branch' => self::bankBranchValidator(),
            'employment_status' => self::employmentStatusValidator(),
            'business_sector' => self::businessSectorValidator(),
            'employer_name' => self::employerNameValidator(),
            'other_business' => self::otherBusinessValidator(),
            'current_occupation' => self::currentOccupationValidator(),
            'k_name' => self::kinNameValidator(),
            'k_mobile' => self::kinMobileValidator(),
            'k_email' => self::kinEmailValidator(),
            'k_relationship' => self::kinRelationshipValidator(),
            'tin' => self::tinValidator(),
            'region' => self::nameValidator(),
            'address' => self::addressValidator(),
            'district' => self::nameValidator(),
            'ward' => self::nameValidator(),
            'place_birth' => self::nameValidator(),
            'flex_acc_no' => self::flexNumberValidator(),
            'risk_status' => self::standardInputValidator(),
            'gender' => self::genderValidator(),
            'title' => self::titleValidator(),
            'dob' => self::dobValidator(),
            'identity' => self::identityValidator(),
            'joint_email' => self::emailValidator(),
            'joint_mobile' => ['required', 'string', 'phone:country'],
//            'joint_mobile' => ['required', 'string'],
            'dse_account' => self::dseAccountValidator(),
            'firstname' => self::nameValidator(),
            'middlename' => [],
            'lastname' => self::nameValidator(),
            'email' => self::emailValidator(),
            'country' => self::countryCodeValidator(),
            'mobile' => ['required', 'string'],
            //            'mobile' => ['required', 'string', 'unique:users,mobile', 'max:20', 'phone:country_code'],
        ];
    }

    public static function newIndividualProfileValidator(): array
    {
        return [
            'bank_account_number' => self::bankAccountNumberValidator(),
            'bank_account_name' => self::bankAccountNameValidator(),
            'bank_branch' => self::bankBranchValidator(),
            'employment_status' => self::employmentStatusValidator(),
            'business_sector' => self::businessSectorValidator(),
            'employer_name' => self::employerNameValidator(),
            'other_business' => self::otherBusinessValidator(),
            'current_occupation' => self::currentOccupationValidator(),
            'k_name' => self::kinNameValidator(),
            'k_mobile' => self::kinMobileValidator(),
            'k_email' => self::kinEmailValidator(),
            'k_relationship' => self::kinRelationshipValidator(),
            'tin' => self::tinValidator(),
            'region' => self::nameValidator(),
            'address' => self::addressValidator(),
            'district' => self::nameValidator(),
            'ward' => self::nameValidator(),
            'place_birth' => self::nameValidator(),
            'flex_acc_no' => self::flexNumberValidator(),
            'risk_status' => self::standardInputValidator(),
            'gender' => self::genderValidator(),
            'title' => self::titleValidator(),
            'dob' => self::dobValidator(),
            'identity' => self::identityValidator(),
            'email' => self::emailValidator(),
            'mobile' => ['required', 'string'],
            'dse_account' => self::dseAccountValidator(),
            'firstname' => self::nameValidator(),
            'middlename' => self::nameValidator(),
            'lastname' => self::nameValidator(),
            'joint_email' => ['required', 'email', 'unique:users,email'],
            'country' => self::countryCodeValidator(),
            'joint_mobile' => ['required', 'string', 'max:20'],
        ];
    }


    public static function passwordValidator(): array
    {
        return ['password' => ['required', Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                  //  ->uncompromised()
                    ]
            ];
    }

    public static function jointProfileValidator(): array
    {
        return [
            'bank_account_number' => self::bankAccountNumberValidator(),
            'bank_account_name' => self::bankAccountNameValidator(),
            'bank_branch' => self::bankBranchValidator(),
            'employment_status' => self::employmentStatusValidator(),
            'business_sector' => self::businessSectorValidator(),
            'employer_name' => self::employerNameValidator(),
            'other_business' => self::otherBusinessValidator(),
            'current_occupation' => self::currentOccupationValidator(),
            'k_name' => self::kinNameValidator(),
            'k_mobile' => self::kinMobileValidator(),
            'k_email' => self::kinEmailValidator(),
            'k_relationship' => self::kinRelationshipValidator(),
            'tin' => self::tinValidator(),
            'region' => self::nameValidator(),
            'address' => self::addressValidator(),
            'district' => self::nameValidator(),
            'ward' => self::nameValidator(),
            'place_birth' => self::nameValidator(),
            'flex_acc_no' => self::flexNumberValidator(),
            'risk_status' => self::standardInputValidator(),
            'gender' => self::genderValidator(),
            'title' => self::titleValidator(),
            'dob' => self::dobValidator(),
            'identity' => self::identityValidator(),
            'email' => self::emailValidator(),
            'mobile' => ['required', 'string', 'max:20', 'phone:country'],
            'dse_account' => self::dseAccountValidator(),
            'firstname' => self::nameValidator(),
            'middlename' => self::nameValidator(),
            'lastname' => self::nameValidator(),
            'joint_email' => self::emailValidator(),
            'country' => self::countryCodeValidator(),
            'joint_mobile' => ['required', 'string', 'max:20', 'phone:country'],

            'j_tin' => self::tinValidator(),
            'j_region' => self::nameValidator(),
            'j_address' => self::addressValidator(),
            'j_district' => self::nameValidator(),
            'j_ward' => self::nameValidator(),
            'j_place_birth' => self::nameValidator(),
            'j_gender' => self::genderValidator(),
            'j_title' => self::nameValidator(),
            'j_dob' => self::dobValidator(),
            'j_identity' => self::identityValidator(),
            'j_firstname' => self::nameValidator(),
            'j_middlename' => self::nameValidator(),
            'j_lastname' => self::nameValidator(),
        ];
    }

    public static function newJointProfileValidator(): array
    {
        return [
            'bank_account_number' => self::bankAccountNumberValidator(),
            'bank_account_name' => self::bankAccountNameValidator(),
            'bank_branch' => self::bankBranchValidator(),
            'employment_status' => self::employmentStatusValidator(),
            'business_sector' => self::businessSectorValidator(),
            'employer_name' => self::employerNameValidator(),
            'other_business' => self::otherBusinessValidator(),
            'current_occupation' => self::currentOccupationValidator(),
            'k_name' => self::kinNameValidator(),
            'k_mobile' => self::kinMobileValidator(),
            'k_email' => self::kinEmailValidator(),
            'k_relationship' => self::kinRelationshipValidator(),
            'tin' => self::tinValidator(),
            'region' => self::nameValidator(),
            'address' => self::addressValidator(),
            'district' => self::nameValidator(),
            'ward' => self::nameValidator(),
            'place_birth' => self::nameValidator(),
            'flex_acc_no' => self::flexNumberValidator(),
            'risk_status' => self::standardInputValidator(),
            'gender' => self::genderValidator(),
            'title' => self::titleValidator(),
            'dob' => self::dobValidator(),
            'identity' => self::identityValidator(),
            'joint_email' => self::emailValidator(),
            'joint_mobile' => ['required', 'string', 'max:20', 'phone:country'],
            'dse_account' => self::dseAccountValidator(),
            'firstname' => self::nameValidator(),
            'middlename' => self::nameValidator(),
            'lastname' => self::nameValidator(),
            'email' => self::emailValidator(),
            'country' => self::countryCodeValidator(),
            'mobile' => ['required', 'string', 'max:20', 'phone:country'],

            'j_tin' => self::tinValidator(),
            'j_region' => self::nameValidator(),
            'j_address' => self::addressValidator(),
            'j_district' => self::nameValidator(),
            'j_ward' => self::nameValidator(),
            'j_place_birth' => self::nameValidator(),
            'j_gender' => self::genderValidator(),
            'j_title' => self::nameValidator(),
            'j_dob' => self::dobValidator(),
            'j_identity' => self::identityValidator(),
            'j_firstname' => self::nameValidator(),
            'j_middlename' => self::nameValidator(),
            'j_lastname' => self::nameValidator(),
        ];
    }

    public static function bondOrderValidator(): array
    {
        return [
            'custodian' => self::userHasCustodianValidator(),
            'bond' => ['required', new BondAvailableValidation()],
            'face_value' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'use_custodian' => ['required'],
            'coupons' => ['required', 'numeric'],
            'notice' => ['nullable', 'string'],
        ];
    }

    public static function buyOrderValidator(): array
    {
        return [
            'custodian' => self::userHasCustodianValidator(),
            'security' => ['required', new SecurityAvailableValidation],
            'volume' => ['required', 'numeric'],
            'price' => ['nullable', 'numeric'],
            'use_custodian' => ['required'],
            'notice' => ['nullable', 'string'],
        ];
    }

    public static function linkCustodiaValidator(): array
    {
        return [
            'id' => self::custodianAvailableValidator(),
        ];
    }

    public static function corporateInformationValidator(): array
    {
        return [
            'corporate_type' => self::titleValidator(),
            'other_corporate_type' => self::nameValidator(),
            'corporate_name' => self::nameValidator(),
            'corporate_telephone' => self::nameValidator(),
            'corporate_email' => self::emailValidator(),
            'corporate_trade_name' => self::nameValidator(),
            'business_sector' => self::businessSectorValidator(),
            'corporate_address' => self::addressValidator(),
            'corporate_building' => self::addressValidator(),
            'corporate_tin' => self::nameValidator(),
            'corporate_reg_number' => self::nameValidator(),
        ];
    }

    public static function personalInformationValidator(): array
    {
        return [
            'title' => self::titleValidator(),
            'position' => self::filledFullNameValidator(),
            'firstname' => self::nameValidator(),
            'lastname' => self::nameValidator(),
            'dob' => self::dobValidator(),
            'gender' => self::genderValidator(),
            'id_type' => self::idTypeValidator(),
            'identity' => self::identityValidator(),
            'tin' => self::tinValidator(),
        ];
    }

    public static function contactInformationValidator(): array
    {
        return [
            'country_code' => self::countryCodeValidator(),
            'mobile' => self::mobileValidator(),
            'nationality' => self::nationalityValidator(),
            'address' => self::addressValidator(),
            'email' => self::emailValidator(),
        ];
    }

    public static function bankInformationValidator(): array
    {
        return [
            'bank_id' => self::bankValidator(),
            'bank_branch' => self::bankBranchValidator(),
            'bank_account_number' => self::bankAccountNumberValidator(),
            'bank_account_name' => self::bankAccountNameValidator(),
        ];
    }

    public static function nextOfKinInformationValidator(): array
    {
        return [
            'name' => self::kinNameValidator(),
            'mobile' => self::kinMobileValidator(),
            'email' => self::kinEmailValidator(),
            'relationship' => self::kinRelationshipValidator(),
        ];
    }

    public static function employmentInformationValidator(): array
    {
        return [
            'employment_status' => self::employmentStatusValidator(),
            'employer_name' => self::employerNameValidator(),
            'current_occupation' => self::currentOccupationValidator(),
            'business_sector' => self::businessSectorValidator(),
        ];
    }

    public static function updateAccountValidator(): array
    {
        return [
            'email' => self::emailUniqueValidator(),
            'mobile' => self::mobileValidator(),
            'dse_account' => self::dseAccountValidator(),
        ];
    }

    public static function updateFilesValidator(): array
    {
        return [
            'signature_file' => self::filledImageFileValidator(),
            'tin_file' => self::filledFileValidator(),
            'identity_file' => self::filledFileValidator(),
            'passport_file' => self::filledImageFileValidator(),
        ];
    }

    public static function dobValidator(): array
    {
        return ['required', 'date', new Age18Validator];
    }

    public static function fullNameValidator(): array
    {
        return ['required', 'string', 'min:3', 'max:300'];
    }

    public static function filledFullNameValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function nameValidator(): array
    {
        return ['string', 'min:3', 'max:30'];
    }

    public static function standardInputValidator(): array
    {
        return ['nullable', 'string', 'min:2', 'max:30'];
    }

    public static function bankAccountNameValidator(): array
    {
        return ['required', 'string', 'min:3', 'max:191'];
    }

    public static function bankAccountNumberValidator(): array
    {
        return ['required', 'string', 'min:3', 'max:191'];
    }

    public static function bankBranchValidator(): array
    {
        return ['required', 'string', 'min:2', 'max:191'];
    }

    public static function titleValidator(): array
    {
        return ['required', 'string', 'min:2', 'max:191'];
    }

    public static function emailValidator(): array
    {
        return ['required', 'email'];
    }

    public static function mobileValidator(): array
    {
        return ['required', 'phone:country_code'];
    }

    public static function emailUniqueValidator(): array
    {
        return ['required', 'email', Rule::unique('users')];
    }

    public static function countryCodeValidator(): array
    {
        return ['required', 'string', new CountryAvailableValidation];
    }

    public static function genderValidator(): array
    {
        return ['required', 'string', Rule::in(['male', 'female', 'MALE', 'FEMALE', 'Female', 'Male'])];
    }

    public static function idTypeValidator(): array
    {
        return ['required', 'string', Rule::in(['NIDA', 'Passport'])];
    }

    public static function identityValidator(): array
    {
        return ['required', 'string', 'max:191'];
    }

    public static function imageFileValidator(): array
    {
        return ['nullable', 'image', 'size:1000'];
    }

    public static function filledImageFileValidator(): array
    {
        return ['nullable', 'image', 'size:1000'];
    }

    public static function fileValidator(): array
    {
        return ['nullable', 'file', 'size:1000'];
    }

    public static function filledFileValidator(): array
    {
        return ['nullable', 'file', 'size:1000'];
    }

    public static function nationalityValidator(): array
    {
        return ['required', 'string', 'min:3', 'max:191'];
    }

    public static function addressValidator(): array
    {
        return ['required', 'string', 'min:3', 'max:300'];
    }

    public static function custodianAvailableValidator(): array
    {
        return ['nullable', new CustodianAvailableValidation];
    }

    public static function employmentStatusValidator(): array
    {
        //        return ['nullable', 'string', 'min:3', 'max:300', Rule::in(Helper::employmentStatus())];
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function employerNameValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function businessSectorValidator(): array
    {
        return ['nullable', 'string', new SectorAvailableValidation];
    }

    public static function otherBusinessValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function otherEmploymentValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function currentOccupationValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function tinValidator(): array
    {
        return ['numeric', 'min:9', new TinValidator];
    }

    public static function nidaValidator(): array
    {
        return ['string', 'min:20', new NidaNumberValidator];
    }

    public static function bankValidator(): array
    {
        return ['required', new BankAvailableValidation];
    }

    public static function dseAccountValidator(): array
    {
        return ['nullable', 'numeric', new DseAccountValidator];
    }

    public static function flexNumberValidator(): array
    {
        return ['nullable', 'numeric', new FlexNumberValidator];
    }

    public static function kinParentValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:191'];
    }

    public static function kinMobileValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:191'];
    }

    public static function kinEmailValidator(): array
    {
        return ['nullable', 'email', 'min:3', 'max:191'];
    }

    public static function kinRelationshipValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:191'];
    }

    public static function kinNameValidator(): array
    {
        return ['nullable', 'string', 'min:3', 'max:300'];
    }

    public static function userValidator(): array
    {
        return ['string', new UserAvailableValidation()];
    }

    public static function userHasCustodianValidator(): array
    {
        return [new UserHasCustodianValidation];
    }

    public static function nidaChecking(string $nida): bool
    {
        return strlen($nida) > 19;
    }
}
