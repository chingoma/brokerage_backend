<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Models\Profile;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\CRM\Entities\CustomerCategory;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $email = str_ireplace("'", '', $row['email']);
            $userStatus = User::where('email', $email)->first();

            $nida = str_ireplace("'", '', $row['p_national_id']);
            $statusNida = Profile::where('identity', $nida)->first();

            $passport = str_ireplace("'", '', $row['passport_no']);
            $statusPass = Profile::where('identity', $passport)->first();

            $queryCountry = str_ireplace("'", '', $row['country']);
            $country = \DB::table('countries')->where('iso2', $queryCountry)->first();

            $queryBank = str_ireplace("'", '', $row['bank_name']);
            $bank = \DB::table('banks')->where('bank_name', $queryBank)->first();

            $queryCategory = str_ireplace("'", '', $row['category']);
            $category = CustomerCategory::where('name', $queryCategory)->first();

            if (strtolower(request()->mode) == 'create') {
                if (empty($userStatus) && empty($statusNida) && empty($statusPass)) {
                    $user = new User();
                    $user->updated_by = request()->header('id');
                    $user->name = str_ireplace("'", '', $row['name']);
                    $user->flex_acc_no = str_ireplace("'", '', $row['flexaccno']);
                    $user->dse_account = str_ireplace("'", '', $row['dse_cds_no']);
                    $user->email = str_ireplace("'", '', $row['email']);
                    $user->mobile = str_ireplace("'", '', $row['mobile']);
                    $user->status = 'pending';
                    $user->type = 'individual';
                    $user->email_verified_at = now()->toDateTimeString();
                    $user->password = Factory::create()->password();
                    $user->self_registration = false;
                    $user->has_custodian = false;
                    $user->custodian_approved = false;
                    $user->custodian = 'No Custodian';
                    $user->bank_name = $bank->bank_name ?? $queryBank;
                    $user->bank_account_name = str_ireplace("'", '', $row['name']);
                    $user->bank_account_number = str_ireplace("'", '', $row['ac_number']);
                    $user->bank_branch = str_ireplace("'", '', $row['branch']);
                    $user->manager_id = $category->manager_id;
                    $user->category_id = $category->id;
                    $user->is_admin = false;
                    $user->save();
                    Helper::customerUID($user);

                    // first applicant
                    $UNIX_DATE = ($row['dob'] - 25569) * 86400;
                    $profile = new Profile();
                    $profile->name = str_ireplace("'", '', $row['name']);
                    $profile->country_id = $country->id ?? '';
                    $profile->nationality = str_ireplace("'", '', $row['nationality']);
                    $profile->address = str_ireplace("'", '', $row['address']);
                    $profile->mobile = str_ireplace("'", '', $row['mobile']);
                    $profile->email = str_ireplace("'", '', $row['email']);
                    $profile->gender = strtolower($row['sex']);
                    $profile->dob = date('Y-m-d', $UNIX_DATE);
                    $profile->tin = str_ireplace("'", '', $row['tin']);
                    $profile->title = str_ireplace("'", '', $row['title']);
                    $profile->identity = str_ireplace("'", '', $row['p_national_id'] ?? $row['passport_no']);
                    $profile->id_type = str_ireplace("'", '', $row['p_national_id'] ? 'NIDA' : 'PASSPORT');
                    $profile->user_id = $user->id;
                    $profile->updated_by = request()->header('id');
                    $profile->save();
                }
            }

            if (strtolower(request()->mode) == 'test') {
                if (empty($userStatus) && empty($statusNida) && empty($statusPass)) {
                    $user = new User();
                    $user->updated_by = request()->header('id');
                    $user->name = str_ireplace("'", '', $row['name']);
                    $user->dse_account = str_ireplace("'", '', $row['dse_cds_no']);
                    $user->flex_acc_no = str_ireplace("'", '', $row['flexaccno']);
                    $user->email = str_ireplace("'", '', $row['email']);
                    $user->mobile = str_ireplace("'", '', $row['mobile']);
                    $user->status = 'pending';
                    $user->type = 'individual';
                    $user->email_verified_at = now()->toDateTimeString();
                    $user->password = Factory::create()->password();
                    $user->self_registration = false;
                    $user->has_custodian = false;
                    $user->custodian_approved = false;
                    $user->custodian = 'No Custodian';
                    $user->bank_name = $bank->bank_name ?? $queryBank;
                    $user->bank_account_name = str_ireplace("'", '', $row['name']);
                    $user->bank_account_number = str_ireplace("'", '', $row['ac_number']);
                    $user->bank_branch = str_ireplace("'", '', $row['branch']);
                    $user->manager_id = $category->manager_id;
                    $user->category_id = $category->id;
                    $user->is_admin = false;
                    $user->save();

                    // first applicant
                    $UNIX_DATE = ($row['dob'] - 25569) * 86400;
                    $profile = new Profile();
                    $profile->name = str_ireplace("'", '', $row['name']);
                    $profile->country_id = $country->id ?? '';
                    $profile->nationality = str_ireplace("'", '', $row['nationality']);
                    $profile->address = str_ireplace("'", '', $row['address']);
                    $profile->mobile = str_ireplace("'", '', $row['mobile']);
                    $profile->email = str_ireplace("'", '', $row['email']);
                    $profile->gender = strtolower($row['sex']);
                    $profile->dob = date('Y-m-d', $UNIX_DATE);
                    $profile->tin = str_ireplace("'", '', $row['tin']);
                    $profile->title = str_ireplace("'", '', $row['title']);
                    $profile->identity = str_ireplace("'", '', $row['p_national_id'] ?? $row['passport_no']);
                    $profile->id_type = str_ireplace("'", '', $row['p_national_id'] ? 'NIDA' : 'PASSPORT');
                    $profile->user_id = $user->id;
                    $profile->updated_by = request()->header('id');
                    $profile->save();
                }
            }

            if (strtolower(request()->mode) == 'update') {
                if (! empty($userStatus)) {
                    $user = $userStatus;
                    $user->updated_by = request()->header('id');
                    $user->dse_account = str_ireplace("'", '', $row['dse_cds_no']);
                    $user->name = str_ireplace("'", '', $row['name']);
                    $user->flex_acc_no = str_ireplace("'", '', $row['flexaccno']);
                    $user->email = str_ireplace("'", '', $row['email']);
                    $user->mobile = str_ireplace("'", '', $row['mobile']);
                    $user->status = 'pending';
                    $user->type = 'individual';
                    $user->bank_name = $bank->bank_name ?? $queryBank;
                    $user->bank_account_name = str_ireplace("'", '', $row['name']);
                    $user->bank_account_number = str_ireplace("'", '', $row['ac_number']);
                    $user->bank_branch = str_ireplace("'", '', $row['branch']);
                    $user->manager_id = $category->manager_id;
                    $user->category_id = $category->id;
                    $user->save();

                    // first applicant
                    $UNIX_DATE = ($row['dob'] - 25569) * 86400;
                    $profile = Profile::where('user_id', $user->id)->first();
                    $profile->name = str_ireplace("'", '', $row['name']);
                    $profile->country_id = $country->id ?? '';
                    $profile->nationality = str_ireplace("'", '', $row['nationality']);
                    $profile->address = str_ireplace("'", '', $row['address']);
                    $profile->mobile = str_ireplace("'", '', $row['mobile']);
                    $profile->email = str_ireplace("'", '', $row['email']);
                    $profile->gender = str_ireplace("'", '', strtolower($row['sex']));
                    $profile->dob = date('Y-m-d', $UNIX_DATE);
                    $profile->tin = str_ireplace("'", '', $row['tin']);
                    $profile->title = str_ireplace("'", '', $row['title']);
                    $profile->identity = str_ireplace("'", '', $row['p_national_id'] ?? $row['passport_no']);
                    $profile->id_type = str_ireplace("'", '', $row['p_national_id'] ? 'NIDA' : 'PASSPORT');
                    $profile->user_id = $user->id;
                    $profile->updated_by = request()->header('id');
                    $profile->save();
                }
            }

        }
    }

    public function rules(): array
    {
        return [
            'identity' => Rule::unique('profiles', 'identity'),
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'identity.unique' => 'Identity  already used',
        ];
    }
}
