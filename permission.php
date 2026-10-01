<?php
/**
 * Laravel Permission Fixer
 *
 * Laravel Root:
 *     Current file location
 *
 * Folder Permission:
 *     755
 *
 * File Permission:
 *     644
 *
 * Writable:
 *     storage/          775
 *     bootstrap/cache/  775
 *
 */


error_reporting(E_ALL);
ini_set('display_errors', 1);


// Laravel root folder
$basePath = __DIR__;



// Folders to skip
$exclude = [
    
];



/**
 * Check excluded folders
 */
function isExcluded($path, $exclude)
{

    foreach ($exclude as $folder) {


        if (
            strpos(
                $path,
                DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR
            ) !== false
        ) {

            return true;

        }


        if (basename($path) == $folder) {

            return true;

        }

    }


    return false;

}





/**
 * Recursive permission fixer
 */
function fixPermission($path, $exclude)
{


    if (isExcluded($path, $exclude)) {

        return;

    }



    if (is_dir($path)) {


        chmod($path, 0755);

        echo "DIR 755  : $path\n";



        $items = scandir($path);



        foreach ($items as $item) {


            if ($item == "." || $item == "..") {

                continue;

            }



            fixPermission(
                $path . DIRECTORY_SEPARATOR . $item,
                $exclude
            );

        }



    } else {


        chmod($path, 0644);

        echo "FILE 644 : $path\n";

    }


}






echo "====================================\n";
echo " Laravel Permission Fix Started\n";
echo "====================================\n\n";





// Root permission

chmod($basePath,0755);





// Normal Laravel files/folders

fixPermission(
    $basePath,
    $exclude
);






/**
 * Writable Laravel folders
 */

$writableFolders = [

    $basePath . '/storage',
    $basePath . '/bootstrap/cache'

];




echo "\nSetting Writable Folders...\n\n";



foreach ($writableFolders as $folder) {



    if (!is_dir($folder)) {

        continue;

    }



    chmod($folder,0775);



    $iterator = new RecursiveIteratorIterator(

        new RecursiveDirectoryIterator(

            $folder,

            RecursiveDirectoryIterator::SKIP_DOTS

        ),

        RecursiveIteratorIterator::SELF_FIRST

    );




    foreach ($iterator as $item) {



        if ($item->isDir()) {


            chmod(
                $item->getPathname(),
                0775
            );


        } else {


            chmod(
                $item->getPathname(),
                0664
            );


        }


    }



    echo "Writable Fixed : $folder\n";


}






// Protect environment file

if (file_exists($basePath.'/.env')) {


    chmod(
        $basePath.'/.env',
        0640
    );


    echo ".env permission : 640\n";

}






// Artisan executable

if (file_exists($basePath.'/artisan')) {


    chmod(
        $basePath.'/artisan',
        0755
    );


    echo "artisan permission : 755\n";

}






echo "\n====================================\n";
echo " Permission Update Completed\n";
echo "====================================\n";

?>
