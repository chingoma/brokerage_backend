<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTables();

        try {
            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();
            $this->banks();
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }

    }

    /**
     * Truncates  table
     */
    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('banks')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function banks(): void
    {
        $banks = [
            ['id' => '00fe055e-0dcd-45d1-a219-de0e667b75f5', 'bic' => 'CORUTZTZ', 'name' => 'CRDB BANK PLC', 'created_at' => '2023-08-17 08:27:47', 'updated_at' => '2023-08-17 08:28:09', 'deleted_at' => null],
            ['id' => '0af9a0c9-cc35-400c-b894-2a5278891ecb', 'bic' => 'UNAFTZTZ', 'name' => 'UBA BANK TZ', 'created_at' => '2023-08-17 08:33:58', 'updated_at' => '2023-08-17 08:33:58', 'deleted_at' => null],
            ['id' => '1', 'bic' => 'ACTZTZTZ', 'name' => 'ACCESSBANK TANZANIA LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '10', 'bic' => 'CFUBTZTZ', 'name' => 'CF UNION BANK LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '11', 'bic' => 'CITITZTZ', 'name' => 'CITIBANK TANZANIA LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '12', 'bic' => 'UNBFTZTZ', 'name' => 'COMMERCIAL BANK OF AFRICA (TANZANIA', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '1296d14b-6fe9-47a8-855b-ec2edd88cae4', 'bic' => 'EXTNTZTZ', 'name' => 'EXIM BANK (TANZANIA) LTD', 'created_at' => '2023-08-17 08:29:05', 'updated_at' => '2023-08-17 08:29:05', 'deleted_at' => null],
            ['id' => '13', 'bic' => 'CORUTZT10T6', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => '2023-08-21 02:07:39', 'deleted_at' => '2023-08-21 02:07:39'],
            ['id' => '14', 'bic' => 'CORUTZT10T5', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => '2023-08-21 02:07:43', 'deleted_at' => '2023-08-21 02:07:43'],
            ['id' => '15', 'bic' => 'CORUTZTZ', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '16', 'bic' => 'CORUTZT10TD', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => '2023-08-21 02:07:47', 'deleted_at' => '2023-08-21 02:07:47'],
            ['id' => '17', 'bic' => 'CORUTZT10T2', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => '2023-08-21 02:07:50', 'deleted_at' => '2023-08-21 02:07:50'],
            ['id' => '18', 'bic' => 'CORUTZT10T3', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => '2023-08-21 02:07:58', 'deleted_at' => '2023-08-21 02:07:58'],
            ['id' => '18d69265-3c6f-4f43-9304-161003783ef6', 'bic' => 'EQBLTZTZ', 'name' => 'EQUITY BANK TANZANIA LIMITED', 'created_at' => '2023-09-06 03:22:42', 'updated_at' => '2023-09-06 03:22:42', 'deleted_at' => null],
            ['id' => '19', 'bic' => 'CORUTZT10T4', 'name' => 'CRDB BANK LIMITED', 'created_at' => null, 'updated_at' => '2023-08-21 02:07:54', 'deleted_at' => '2023-08-21 02:07:54'],
            ['id' => '199b81ec-a78f-485d-959e-f7fdbb4ca416', 'bic' => 'DTKETZTZ', 'name' => 'DIAMOND TRUST BANK TANZANIA LTD', 'created_at' => '2023-08-17 08:28:43', 'updated_at' => '2023-08-17 08:28:43', 'deleted_at' => null],
            ['id' => '2', 'bic' => 'FMBZTZTX', 'name' => 'AFRICAN BANKING CORPORATION TANZANI', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '20', 'bic' => 'CFLLTZT1', 'name' => 'CROWN FINANCE AND LEASING LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '21', 'bic' => 'DASUTZTZ', 'name' => 'DAR ES SALAAM COMMUNITY BANK LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '22', 'bic' => 'DTKETZTZ', 'name' => 'DIAMOND TRUST BANK TANZANIA LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '23', 'bic' => 'AFDETZT1', 'name' => 'EAST AFRICAN DEVELOPMENT BANK', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '24', 'bic' => 'EUAFTZTZ', 'name' => 'EURAFRICAN BANK (TANZANIA) LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '25', 'bic' => 'EXTNTZTZ', 'name' => 'EXIMBANK (TANZANIA) LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '26', 'bic' => 'FBMETZTZ', 'name' => 'FBME BANK LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '27', 'bic' => 'ADBCTZT1', 'name' => 'FIRST ADILI BANCORP LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '28', 'bic' => 'HABLTZTZ', 'name' => 'HABIB AFRICAN BANK', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '29', 'bic' => 'BKMYTZTZ', 'name' => 'INTERNATIONAL COMMERCIAL BANK (TANZ', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '3', 'bic' => 'AKCOTZTZ', 'name' => 'AKIBA COMMERCIAL BANK LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '30', 'bic' => 'KACLTZT1', 'name' => 'KARADHA COMPANY LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '31', 'bic' => 'KCBLTZTZ', 'name' => 'KENYA COMMERCIAL BANK (TANZANIA) LT', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '31d70f3d-6059-4efa-9d00-071c526a3863', 'bic' => 'new VIC', 'name' => 'new Bank', 'created_at' => '2023-09-19 00:33:37', 'updated_at' => '2023-12-13 05:02:18', 'deleted_at' => '2023-12-13 05:02:18'],
            ['id' => '32', 'bic' => 'KLMJTZT1', 'name' => 'KILIMANJARO COOPERATIVE BANK LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '33', 'bic' => 'NLCBTZTX0TM', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:08:58', 'deleted_at' => '2023-08-21 02:08:58'],
            ['id' => '34', 'bic' => 'NLCBTZTXFIN', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '35', 'bic' => 'NLCBTZTX0T8', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:01', 'deleted_at' => '2023-08-21 02:09:01'],
            ['id' => '36', 'bic' => 'NLCBTZTX0T5', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:04', 'deleted_at' => '2023-08-21 02:09:04'],
            ['id' => '37', 'bic' => 'NLCBTZT10T6', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:08', 'deleted_at' => '2023-08-21 02:09:08'],
            ['id' => '38', 'bic' => 'NLCBTZT10T7', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:08:41', 'deleted_at' => '2023-08-21 02:08:41'],
            ['id' => '39', 'bic' => 'NLCBTZTX0T4', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:26', 'deleted_at' => '2023-08-21 02:09:26'],
            ['id' => '39ec5354-3a83-4648-9275-8e9611c6b030', 'bic' => 'SCBLTZTX', 'name' => 'STANDARD CHARTERED BANK', 'created_at' => '2023-08-17 08:32:14', 'updated_at' => '2023-08-17 08:32:14', 'deleted_at' => null],
            ['id' => '4', 'bic' => 'AMNNTZTZ', 'name' => 'AMANA BANK LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '40', 'bic' => 'NLCBTZTX0T3', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:16', 'deleted_at' => '2023-08-21 02:09:16'],
            ['id' => '41', 'bic' => 'NLCBTZTXMOR', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:08:36', 'deleted_at' => '2023-08-21 02:08:36'],
            ['id' => '42', 'bic' => 'NLCBTZTXTAN', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:08:31', 'deleted_at' => '2023-08-21 02:08:31'],
            ['id' => '43', 'bic' => 'NLCBTZTX', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '44', 'bic' => 'NLCBTZTXMBE', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:34', 'deleted_at' => '2023-08-21 02:09:34'],
            ['id' => '45', 'bic' => 'NLCBTZTXZAN', 'name' => 'NATIONAL BANK OF COMMERCE, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:08:27', 'deleted_at' => '2023-08-21 02:08:27'],
            ['id' => '46', 'bic' => 'NABHTZT1', 'name' => 'NATIONAL BUREAU DE CHANGE LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '47', 'bic' => 'NMIBTZTZ', 'name' => 'NMB BANK', 'created_at' => null, 'updated_at' => '2023-08-17 08:30:42', 'deleted_at' => null],
            ['id' => '48', 'bic' => 'PBZATZTZCHK', 'name' => 'PEOPLES BANK OF ZANZIBAR, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:45', 'deleted_at' => '2023-08-21 02:09:45'],
            ['id' => '49', 'bic' => 'PBZATZTZ', 'name' => 'PEOPLES BANK OF ZANZIBAR, THE', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '5', 'bic' => 'AZANTZTZ', 'name' => 'AZANIA BANCORP', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '50', 'bic' => 'PBZATZTZNWK', 'name' => 'PEOPLES BANK OF ZANZIBAR, THE', 'created_at' => null, 'updated_at' => '2023-08-21 02:09:50', 'deleted_at' => '2023-08-21 02:09:50'],
            ['id' => '51', 'bic' => 'SFICTZTZ', 'name' => 'SAVINGS AND FINANCE COMMERCIAL BANK', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '52', 'bic' => 'SBICTZTX', 'name' => 'STANBIC BANK TANZANIA LTD.', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '53', 'bic' => 'SCBLTZTX', 'name' => 'STANDARD CHARTERED BANK TANZANIA LT', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '5313b61a-3bf3-4dc2-949e-0598c7078bca', 'bic' => 'DTKETZTZ', 'name' => 'DIAMOND TRUST BANK TANZANIA LTD', 'created_at' => '2023-08-17 07:00:07', 'updated_at' => '2023-08-17 07:00:07', 'deleted_at' => null],
            ['id' => '54', 'bic' => 'SBINTZT1', 'name' => 'STATE BANK OF INDIA', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '55', 'bic' => 'TAHOTZT10T2', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:11', 'deleted_at' => '2023-08-21 02:10:11'],
            ['id' => '56', 'bic' => 'TAHOTZT10T7', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:14', 'deleted_at' => '2023-08-21 02:10:14'],
            ['id' => '57', 'bic' => 'TAHOTZT10T4', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:06', 'deleted_at' => '2023-08-21 02:10:06'],
            ['id' => '58', 'bic' => 'TAHOTZT10T6', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:18', 'deleted_at' => '2023-08-21 02:10:18'],
            ['id' => '59', 'bic' => 'TAHOTZT10T5', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:04', 'deleted_at' => '2023-08-21 02:10:04'],
            ['id' => '6', 'bic' => 'BNKMTZTZ', 'name' => 'BANK M TANZANIA LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '60', 'bic' => 'TAHOTZT10T8', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:39', 'deleted_at' => '2023-08-21 02:10:39'],
            ['id' => '61', 'bic' => 'TAHOTZT10T3', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:31', 'deleted_at' => '2023-08-21 02:10:31'],
            ['id' => '62', 'bic' => 'TAHOTZT1', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '63', 'bic' => 'TAHOTZT10TA', 'name' => 'TANZANIA HOUSING BANK', 'created_at' => null, 'updated_at' => '2023-08-21 02:10:25', 'deleted_at' => '2023-08-21 02:10:25'],
            ['id' => '64', 'bic' => 'TAINTZTZ', 'name' => 'TANZANIA INVESTMENT BANK LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '65', 'bic' => 'TAPBTZTZ', 'name' => 'TANZANIA POSTAL BANK', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '66', 'bic' => 'TRBATZT1', 'name' => 'TRUST BANK (TANZANIA) LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '67', 'bic' => 'TWIGTZTZ', 'name' => 'TWIGA BANCORP LIMITED', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '68', 'bic' => 'ULCTTZT1', 'name' => 'ULC (TANZANIA) LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '7', 'bic' => 'BARBTZTZ', 'name' => 'BANK OF BARODA (TANZANIA) LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '8', 'bic' => 'TANZTZTX', 'name' => 'BANK OF TANZANIA', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => '820117a8-4bb0-4e9c-bd1a-8efe611dfa51', 'bic' => 'DTKETZTZ', 'name' => 'DIAMOND TRUST BANK TANZANIA LTD', 'created_at' => '2023-08-17 07:01:26', 'updated_at' => '2023-08-17 07:01:26', 'deleted_at' => null],
            ['id' => '9', 'bic' => 'BARCTZTZ', 'name' => 'BARCLAYS BANK TANZANIA LTD', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 'c2e7c05d-04df-4c9c-b294-a618e79ed3d2', 'bic' => 'BARCTZTZ', 'name' => 'ABSA BANK TANZANIA LIMITED X', 'created_at' => '2023-08-21 02:07:02', 'updated_at' => '2023-09-19 00:33:49', 'deleted_at' => null],
            ['id' => 'd2292772-4dd4-4b8b-8aa7-fd5bdd0f6d87', 'bic' => 'CFUBTZTZ', 'name' => 'I &M BANK (T) LTD', 'created_at' => '2023-08-17 08:29:47', 'updated_at' => '2023-08-17 08:29:47', 'deleted_at' => null],
            ['id' => 'ff8e9c46-2a80-484e-9c2f-543ad895a028', 'bic' => 'NLCBTZTX', 'name' => 'NBC BANK LIMITED', 'created_at' => '2023-08-17 08:31:27', 'updated_at' => '2023-08-17 09:21:17', 'deleted_at' => null],
        ];
        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}
