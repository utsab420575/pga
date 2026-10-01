<?php
/**
 * Laravel Permission Fixer
 *
 * Folder: 755
 * File:   644
 *
 * Writable:
 * storage/          775
 * bootstrap/cache/  775
 *
 */


error_reporting(E_ALL);
ini_set('display_errors', 1);


$basePath = __DIR__;



$exclude = [
    '.git',
    '.idea',
    'node_modules',
    'vendor'
];



function isExcluded($path, $exclude)
{

    foreach ($exclude as $folder) {

        if (
            strpos(
                $path,
                DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR
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





function fixPermission($path, $exclude)
{

    if(isExcluded($path,$exclude)){
        return;
    }



    if(is_dir($path)){


        chmod($path,0755);

        echo "DIR 755 : ".$path."\n";


        $items=scandir($path);


        foreach($items as $item){


            if($item=="." || $item==".."){
                continue;
            }


            fixPermission(
                $path.DIRECTORY_SEPARATOR.$item,
                $exclude
            );

        }


    }
    else{


        chmod($path,0644);

        echo "FILE 644: ".$path."\n";

    }


}





echo "=================================\n";
echo " Laravel Permission Fix Started\n";
echo "=================================\n\n";



// Root

chmod($basePath,0755);



// Normal Laravel permission

fixPermission(
    $basePath,
    $exclude
);




// Laravel writable folders

$writable = [

    $basePath.'/storage',
    $basePath.'/bootstrap/cache'

];



echo "\nSetting writable folders...\n\n";


foreach($writable as $folder){


    if(is_dir($folder)){


        chmod($folder,0775);


        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $folder,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );



        foreach($iterator as $item){


            if($item->isDir()){

                chmod(
                    $item->getPathname(),
                    0775
                );

            }
            else{

                chmod(
                    $item->getPathname(),
                    0664
                );

            }


        }


        echo "Writable fixed: ".$folder."\n";


    }

}




// Protect .env

if(file_exists($basePath.'/.env')){

    chmod(
        $basePath.'/.env',
        0640
    );

    echo ".env permission set 640\n";

}



echo "\n=================================\n";
echo " Laravel Permission Completed\n";
echo "=================================\n";


?>
