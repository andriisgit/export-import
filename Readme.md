# Magento 1.9 export/import tool 
##### This tool uses predetermined attributes 
##### Attribute Set and Attributes should be created manually before import starts

File list:

- `gd.php` - this deep thought name means "GetData". It loads data from Magento pointed on line 3 and writes to binary file pointed on line 130.


- `Boxup.php` - tool for import Products with custom attribute values and images.
Configure all settings in `_construct`


- `marantz.bin` - already exported file. contains 108 items

###### Example of using:
- For export: `php gd.php` as a result, file `marantz.bin` will be created in the current directory

- For import: `php Boxup.php`

Keep in mind that Custom Attributes should be created manually before import starts. "Manually" - becouse I didn't find the tool that creates correctly. The problem I faced to is Configurable Attributes lost their indexes and became unreach for `setAttributeSetId` function