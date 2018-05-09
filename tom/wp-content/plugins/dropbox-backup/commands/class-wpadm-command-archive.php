<?php


class WPadm_Command_Archive extends WPAdm_Сommand{
    public function execute(WPAdm_Command_Context $context)
    {
		ini_set("memory_limit", "256M");
        require_once WPAdm_Core::getPluginDir() . '/modules/class-wpadm-archive.php';
        $af = $this->getArchiveName($context->get('to_file'));
		ini_set("memory_limit", "256M");
		$archive = new WPAdm_Archive($af, $context->get('to_file') . '.md5');
        $archive->setRemovePath($context->get('remove_path'));
        $files = $context->get('files'); 
        if ( !file_exists( $af ) ) {
            WPAdm_Core::log(__('Create part ','dropbox-backup') . basename( $af ) );
        }
        if (file_exists($af) && filesize($af) > $context->get('max_file_size')) {
            $af = $this->getNextArchiveName($context->get('to_file'));
            unset($archive);
            if ( !file_exists( $af ) ) {
                WPAdm_Core::log(__('Create part ','dropbox-backup') . basename( $af ) );
            }
            $archive = new WPAdm_Archive($af, $context->get('to_file') . '.md5');
            $archive->setRemovePath($context->get('remove_path'));
        }
        $files_str = implode(',', $files);
        $files_archive = WPAdm_Running::getCommandResultData('archive');
        if (!in_array($files_str, $files_archive)) {
            $archive->add($files_str);
            $files_archive = WPAdm_Running::getCommandResultData('archive');
            $files_archive[] = $files_str;
            if (!empty($files_archive)) {
                WPAdm_Running::setCommandResultData('archive', $files_archive);
            }
        }
        return true;
    }

    private function getArchiveName($name)
    {
        $archives = glob("{$name}-*.zip");
        if (empty($archives)) {
            return "{$name}-1.zip";
        }
        $n = count($archives);
        $f = "{$name}-{$n}.zip";
        return $f;
    }

    private function getNextArchiveName($name)
    {
        $archives = glob("{$name}-*.zip");
        $n = 1 + count($archives);
        $a = "{$name}-{$n}.zip";
        return $a;
    }
}