# Wysiwyg Magento 1.9.4 fix



### Fix the regeneration of thumbails in wysiwyg magento 1.9.4 


After we upgraded to Magento 1.9.4 we started to experince a high consume of resources and drop in websites performance. 

After a bit of research we found a change in the way how the Magento handles the thumbnails that are displayed in wysiwyg which was introduced with version 1.9.4 in this commit:

https://github.com/OpenMage/magento-mirror/commit/9ffa3a0d2b3070352bf8a4613046e4764d8e4e9e#diff-5982454da8bff4fe6f3efcc442c0dacc
