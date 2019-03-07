<?php

/**
 * Modiefied Wysiwyg Images model to fix the getThumbnailUrl() function 
 *
 * @category    Osmage
 * @package     Osmage_Mycms
 * @author      Sergiu <sergiu@osmage.com>
 */
class Networld_Catalogutils_Model_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    
    /**
     * Return files
     *
     * Override as by default Magento is failing to find the thumbnail and instead re-generating each time
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $files = Mage::getModel('core/file_storage_database')->getDirectoryFiles($path);
            
            $fileStorageModel = Mage::getModel('core/file_storage_file');
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }
        
        $collection = $this->getCollection($path)
            ->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(false)
            ->setOrder('mtime', Varien_Data_Collection::SORT_ORDER_ASC);
        
        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }
        
        $helper = $this->getHelper();
        
        // prepare items
        foreach ($collection as $item) {
            $item->setId($helper->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($helper->getShortFilename($item->getBasename()));
            $item->setUrl($helper->getCurrentUrl() . $item->getBasename());
            
            if ($this->isImage($item->getBasename())) {
                ###########################################################################################################
                ###        Initial code:                                                                                ###
                ###                                                                                                     ###
                ###   $thumbUrl = $this->getThumbnailUrl(                                                               ###
                ###                     Mage_Core_Model_File_Uploader::getCorrectFileName($item->getFilename()),        ###
                ###                     true                                                                            ###
                ###                 );                                                                                  ###
                ###########################################################################################################

                // had to remove the Mage_Core_Model_File_Uploader::getCorrectFileName()
                // as it was returning the path with underscore
                // eg: _var_www_project_magento_media_wysiwyg
                $thumbUrl = $this->getThumbnailUrl(
                    $item->getFilename(), 
                    true);
                
                // generate thumbnail "on the fly" if it does not exists
                if(! $thumbUrl) {
                    $thumbUrl = Mage::getSingleton('adminhtml/url')->getUrl('*/*/thumbnail', array('file' => $item->getId()));
                }
                
                $size = @getimagesize($item->getFilename());
                
                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                $thumbUrl = Mage::getDesign()->getSkinBaseUrl() . self::THUMB_PLACEHOLDER_PATH_SUFFIX;
            }
            
            $item->setThumbUrl($thumbUrl);
        }
        
        return $collection;
    }
}
