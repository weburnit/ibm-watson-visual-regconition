<?PHP

namespace IBMWatson\Messages;

class ClassifierBuilder
{

    /**
     * Compress array of files and return zip file location
     *
     * @param array  $files
     * @param string $destination
     *
     * @return string
     */
    public function compressFiles($files = [], $destination = '')
    {
        $valid_files = [];
        touch($destination);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }
        $zip = new \ZipArchive();
        if ($zip->open($destination, \ZIPARCHIVE::CREATE)) {
            foreach ($valid_files as $file) {
                $zip->addFile($file, $file);
            }
            $zip->close();
        }

        return $destination;
    }
}
