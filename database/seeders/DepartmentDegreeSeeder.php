<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentDegreeSeeder extends Seeder
{
    /**
     * Seed the existing hardcoded department → degree mappings
     * into the new department_degree pivot table.
     *
     * Department IDs (from departments table):
     *  1=CE, 2=EEE, 3=ME, 4=CSE, 5=TE, 6=FE, 7=IPE,
     *  8=IWE, 9=IICT, 10=IEE, 11=Chemistry, 12=Mathematics,
     *  13=Physics, 14=HSS
     *
     * Degree IDs (from degrees table):
     *  1=PGD, 2=M Sc., 3=M Sc. in WEM, 4=M in WEM,
     *  5=M Engg., 6=M Sc. Engg., 7=M Phil., 8=Ph.D,
     *  9=M Sc. Engg (Water & Env), 10=M Engg. (Water & Env),
     *  11=PG. Dip. Engg. (Water & Env), 12=MA (Applied Linguistics),
     *  13=M Sc. (Economics), 14=MBA
     */
    public function run()
    {
        $map = [
            1  => [5, 6, 8],           // CE
            2  => [5, 6, 8],           // EEE
            3  => [5, 6, 8],           // ME
            4  => [5, 6, 8],           // CSE
            5  => [5, 6, 8],           // TE
            6  => [5, 6],              // FE
            7  => [5, 6],              // IPE
            8  => [1, 3, 4, 9, 10, 11],// IWE
            9  => [5, 6, 1],           // IICT
            10 => [5, 6, 1, 8],        // IEE
            11 => [2, 7, 8],           // Chemistry
            12 => [2, 7, 8],           // Mathematics
            13 => [2, 7, 8],           // Physics
            14 => [12, 13, 14],        // HSS
        ];

        $rows = [];
        $now  = now();

        foreach ($map as $deptId => $degreeIds) {
            foreach ($degreeIds as $degreeId) {
                $rows[] = [
                    'department_id' => $deptId,
                    'degree_id'     => $degreeId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        // Insert, ignore duplicates (safe to re-run)
        DB::table('department_degree')->insertOrIgnore($rows);
    }
}
