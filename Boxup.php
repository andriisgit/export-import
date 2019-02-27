<?php
namespace Shopperdknew;

class Boxup
{
    /**
     * @var array $P
     * @var string $appMage
     * @var string $dataFile
     * @var array $prodIds Array of product id mapping. Id - newly created product, value - the old one
     */
    private $P, $appMage, $dataFile, $prodIds, $imgPath;
    protected $attrSetId, $configurableOptionName, $configurableAttributId;

    public function __construct()
    {
        $this->appMage = '../magento.local/app/Mage.php';
        $this->imgPath = 'C:/xampp/htdocs/shopperdknew/media/catalog/product';
        $this->dataFile = 'marantz.bin';

        $this->attrSetId = 10;
        $this->configurableOptionName = 'Farve';

        $this->configurableAttributId = 142;

        $this->run();
    }


    protected function run()
    {
        $this->initMage();
        $this->getProdData();

        $this->SimpleProdBoxUp();
        $this->ConfigurableProdBoxUp();

        //$this->getAttrList();
    }


    protected function SimpleProdBoxUp()
    {
        $this->prodIds = Array();

        echo 'Starting adding Simple products ... ' . PHP_EOL;

        foreach ($this->P as $i=>$_p) {
            if (is_numeric($i)) {

                if ($_p['type'] == 'simple') {
                    $simpleProduct = \Mage::getModel('catalog/product');
                    try {
                        $simpleProduct
                            //->setStoreId(0)
                            ->setWebsiteIds(array(\Mage::app()->getStore(true)->getWebsite()->getId()))//website ID the product is assigned to, as an array
                            ->setAttributeSetId($this->attrSetId)
                            ->setTypeId('simple')//product type
                            ->setCreatedAt(strtotime('now'))//product creation time

                            ->setSku($_p['sku'])//SKU
                            ->setName($_p['name'])//product name
                            ->setWeight($_p['weight'])
                            ->setStatus($_p['status'])//product status (1 - enabled, 2 - disabled)
                            ->setPrice($_p['price'])//price in form 11.22
                            ->setDescription($_p['description'])
                            ->setShortDescription($_p['short_description'])
                            ->setStockData(array(
                                    'use_config_manage_stock' => $_p['stock_data']['use_config_manage_stock'], //'Use config settings' checkbox
                                    'manage_stock' => $_p['stock_data']['manage_stock'], //manage stock
                                    'min_sale_qty' => $_p['stock_data']['min_sale_qty'], //Minimum Qty Allowed in Shopping Cart
                                    'max_sale_qty' => $_p['stock_data']['max_sale_qty'], //Maximum Qty Allowed in Shopping Cart
                                    'is_in_stock' => $_p['stock_data']['is_in_stock'], //Stock Availability
                                    'qty' => $_p['stock_data']['qty'] //qty
                                )
                            )
                            ->setTaxClassId(2)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                            ->setVisibility($this->getVisibilityId($_p['attr']['visibility']['value']))//catalog and search visibility
                            /*
                            ->setManufacturer(21) //manufacturer id
                            ->setColor(22)
                            ->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)
                            ->setCost(22.33) //price in form 11.22
                            ->setSpecialPrice(00.44) //special price in form 11.22
                            */
                            ->setMetaTitle($_p['attr']['meta_title'])
                            ->setMetaKeyword($_p['attr']['meta_keyword'])
                            ->setMetaDescription($_p['attr']['meta_description'])
                            ->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
                            ->setCategoryIds(array(1)); //assign product to categories

                        // Selected type attributes
                        $selAttrArray = [
                          'farve', 'stik', 'kabinet', 'dimensioner', 'bluetooth1',
                          'pladespillerindgang', 'bluetooth_stereo', 'wifi_anslutning', 'airplay', 'spotifyconnect',
                          'fjernbetjening', 'trigger_12v', 'ethernet', 'niveau_kontrol', 'sw_lfe_janej'
                        ];
                        foreach ($selAttrArray as $selAttr) {
                            $attr_id = null;
                            if (isset($this->P[$selAttr])) {
                                foreach ($this->P[$selAttr] as $item) {
                                    if ($item['label'] == $_p['attr'][$selAttr]['value']) {
                                        $attr_id = $item['value'];
                                        break;
                                    }
                                }
                                $simpleProduct->setData($selAttr, $this->getNewFarveId($selAttr, $attr_id));
                            } else {
                                echo '   no data found for "' . $selAttr . '"' . PHP_EOL;
                            }
                        }

                        //flat type attributes
                        $selAttrArray = [
                            'ampbud_input_imedance', 'ampbus_input', 'ampbus_input_sensitivity', 'ampbus_output_impedance', 'ampbus_output_level', 'analoge_linjeindgange',
                            'analoge_linjeindgange', 'anbefaleteffekt', 'anmeldelser', 'atmos', 'automatisk_standby', 'basenhed',
                            'basenhed', 'basmellemtone', 'bassextension', 'batteri_janej_tid', 'biamp', 'biasvoltage',
                            'biasvoltage', 'biwiring', 'cable', 'cable_end', 'cd_drev', 'channel_imbalance',
                            'channel_imbalance', 'channel_imbalance_effekt', 'channel_separation', 'cjm_expecdate', 'cjm_hideshipdate', 'cjm_preorderdate',
                            'cjm_preorderdate', 'cjm_preordertext', 'cjm_ships_in', 'cjm_stockmessage', 'cjm_stocktext', 'compliance',
                            'compliance', 'country_of_manufacture', 'crossover', 'crosstalk', 'curve_accuracy', 'custom_design',
                            'custom_design', 'custom_design_from', 'custom_design_to', 'custom_layout_update', 'customtab', 'customtabtitle',
                            'customtabtitle', 'da_konverter_ja_nej', 'dac', 'dac_ic', 'damping_factor', 'dc_loop_resistance',
                            'dc_loop_resistance', 'densen_opgraderingsmuligheder', 'device', 'diameter', 'digitale_indgange', 'digitale_udgange',
                            'digitale_udgange', 'diskant', 'dispersion', 'distortion_elektrostat', 'driver_system', 'dynamic_range',
                            'dynamic_range', 'effekt', 'effekt_2ohm', 'effekt_6_ohm', 'effekt_power_handling', 'effekt4ohm',
                            'effekt4ohm', 'effekt8ohm', 'electrostaticcapacitance', 'enhed_bl', 'enhed_cms', 'enhed_effekt',
                            'enhed_effekt', 'enhed_effekt_max', 'enhed_electrical_xmax', 'enhed_fo', 'enhed_impedans', 'enhed_levc',
                            'enhed_levc', 'enhed_mmd', 'enhed_mms', 'enhed_no', 'enhed_qes', 'enhed_qms',
                            'enhed_qms', 'enhed_qts', 'enhed_revc', 'enhed_sd', 'enhed_splo', 'enhed_type',
                            'enhed_type', 'enhed_vas', 'enhed_xmax', 'factbox', 'fasejustering', 'foelsomhed',
                            'foelsomhed', 'format_cd', 'forstaerker_type', 'frekvensomraade', 'frekvensomraade_freq_response', 'freqency_response_phono_riaa',
                            'freqency_response_phono_riaa', 'frequency_response', 'frequency_response_line', 'gain', 'gui', 'has_options',
                            'has_options', 'hdam', 'hdmi', 'hdmi_info', 'hoejtaler_type_elektrostat', 'horetelefon_udgang',
                            'horetelefon_udgang', 'image_label', 'impedans', 'indgange', 'indput_sensitivity_phono', 'input_balanced_highlvl',
                            'input_balanced_highlvl', 'input_capacitance', 'input_impedance', 'input_impedance_linje', 'input_impedance_phono_inputs', 'input_sensitivity',
                            'input_sensitivity', 'input_sensitivity_highlvl', 'intermodulation_distortion', 'is_recurring', 'justering_crossover', 'kategori',
                            'kategori', 'kategori_sub', 'l_r_channel_diffrence', 'linearity', 'linje_utgang', 'load_impedance',
                            'load_impedance', 'losless_format', 'lossy_format', 'maal__sub', 'maal_forstaerker', 'manufacturer',
                            'manufacturer', 'materiale', 'max_indput_elektrostat', 'max_sampling_frequency', 'maxspl', 'mechanical_xmax',
                            'mechanical_xmax', 'microphone', 'midrange', 'monteringsmhul', 'motor', 'multiroom',
                            'multiroom', 'news_from_date', 'news_to_date', 'oevrigt', 'oplosning_bit', 'options_container',
                            'options_container', 'output_impedance', 'output_level', 'output_voltage', 'ovrigt_stereo', 'parallel_capacitance',
                            'parallel_capacitance', 'pc_delivery_cost', 'pc_delivery_time', 'pc_eanorupc_prod', 'pc_isbn_prod', 'pc_manufacturer_sku',
                            'pc_manufacturer_sku', 'pc_reitaler_message', 'peak_output_current', 'power', 'power_amplifier_section', 'power_consump',
                            'power_consump', 'power_consump_standby', 'poweroutput', 'pre_amplifier_section', 'pre_out', 'presets',
                            'presets', 'processor_loop', 'pwr_ctrl', 'pwr_tansformer', 'radio_bands', 'rated_max_power_output_thd',
                            'rated_max_power_output_thd', 'rca_output_impedance', 'rca_output_level', 'rec_cate_id', 'rec_cate_id_2', 'rec_cate_id_3',
                            'rec_cate_id_3', 'rec_cate_price_from', 'rec_cate_price_from_2', 'rec_cate_price_from_3', 'rec_cate_price_to', 'rec_cate_price_to_2',
                            'rec_cate_price_to_2', 'rec_cate_price_to_3', 'recurring_profile', 'required_options', 'reviewsfull', 'sampling_frequency',
                            'sampling_frequency', 'searchindex_weight', 'series_inductance', 'shelving_adj', 'short_description', 'shortparams',
                            'shortparams', 'signal_to_noise_ratio', 'signal_to_noise_ratio_line', 'signal_to_noise_ratio_phono_mc', 'signal_to_noise_ratio_phono_mm', 'slope_adj',
                            'slope_adj', 'special_from_date', 'special_price', 'special_to_date', 'speed', 'sps',
                            'sps', 'stn_ratio', 'stn_ratio_highlvl', 'stroemforsyning', 'stylus', 'sub_udgang',
                            'sub_udgang', 'sw_enhedstype', 'sw_indput_impedance', 'sw_indput_sensitivity', 'tape_output_impedance', 'tape_output_level',
                            'tape_output_level', 'terminaler', 'tier_price', 'tier_price_for_store', 'tilbudsskilt', 'tone_control',
                            'tone_control', 'total_harmonic_dist_line', 'total_harmonic_dist_phono', 'total_harmonic_distortion_thd', 'tracking_force', 'transient_intermodulation_dist',
                            'transient_intermodulation_dist', 'udgange', 'unit', 'usb_dsd', 'video_inputs', 'videobox',
                            'videobox', 'voice_coil'
                        ];
                        foreach ($selAttrArray as $selAttr) {
                            if (isset($_p['attr'][$selAttr]) && !is_null($_p['attr'][$selAttr]['value'])) {
                                $simpleProduct->setData($selAttr, $_p['attr'][$selAttr]['value']);
                            }
                        }

                        //load gallery images
                        if (isset($_p['media_gal'])) {
                            foreach ($_p['media_gal'] as $img) {
                                $img = $this->imgPath . $img;
                                if (file_exists($img)) {
                                    $simpleProduct->addImageToMediaGallery($img, 'image', false);
                                } else {
                                    echo '   image file not found for sku ' . $_p['sku'] . ' in ' . $img . PHP_EOL;
                                }
                            }
                        }

                        $simpleProduct->save();

                        $this->prodIds[$_p['id']] = (int)$simpleProduct->getId();

                        $simpleProduct->unsetData();
                    } catch (\Exception $e) {
                        \Mage::log($e->getMessage());
                        echo $e->getMessage();
                    }

                }
            }
        }
    }

    public function ConfigurableProdBoxUp()
    {
        echo 'Starting adding Configurable products ... ';
        foreach ($this->P as $i=>$_p) {
            if (is_numeric($i)) {

                if ($_p['type'] == 'configurable') {

                    echo ' Handhing array\'s item ' . $i . ' ...' . PHP_EOL;

                    $configProduct = \Mage::getModel('catalog/product');
                    try {
                        $configProduct
                            // ->setStoreId(1) //you can set data in store scope
                            ->setWebsiteIds(array(\Mage::app()->getStore(true)->getWebsite()->getId()))//website ID the product is assigned to, as an array
                            ->setAttributeSetId($this->attrSetId)
                            ->setTypeId('configurable')//product type
                            ->setCreatedAt(strtotime('now'))//product creation time

                            // ->setUpdatedAt(strtotime('now')) //product update time
                            ->setSku($_p['sku'])
                            ->setName($_p['name'])//product name
                            ->setWeight($_p['weight'])
                            ->setStatus($_p['status'])//product status (1 - enabled, 2 - disabled)
                            ->setPrice($_p['price'])//price in form 11.22
                            ->setDescription($_p['description'])
                            ->setShortDescription($_p['short_description'])
                            ->setStockData(array(
                                    'use_config_manage_stock' => $_p['stock_data']['use_config_manage_stock'], //'Use config settings' checkbox
                                    'manage_stock' => $_p['stock_data']['manage_stock'], //manage stock
                                    'is_in_stock' => $_p['stock_data']['is_in_stock'], //Stock Availability
                                )
                            )
                            ->setTaxClassId(2)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                            ->setVisibility($this->getVisibilityId($_p['attr']['visibility']['value']))//catalog and search visibility

                            ->setMetaTitle($_p['attr']['meta_title'])
                            ->setMetaKeyword($_p['attr']['meta_keyword'])
                            ->setMetaDescription($_p['attr']['meta_description'])
                            /*
                            ->setManufacturer(28)//manufacturer id
                            ->setNewsFromDate('06/26/2014')//product set as new from
                            ->setNewsToDate('06/30/2014')//product set as new to
                            ->setCountryOfManufacture('AF')//country of manufacture (2-letter country code)
                            ->setCost(22.33)//price in form 11.22
                            ->setSpecialPrice(00.44)//special price in form 11.22
                            ->setSpecialFromDate('06/1/2014')//special price from (MM-DD-YYYY)
                            ->setSpecialToDate('06/30/2014')//special price to (MM-DD-YYYY)
                            ->setMsrpEnabled(1)//enable MAP
                            ->setMsrpDisplayActualPriceType(1)//display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                            ->setMsrp(99.99)//Manufacturer's Suggested Retail Price
                            */
                            ->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization

                            ->setCategoryIds(array(1)) //assign product to categories
                        ;
                        /**/
                        /** assigning associated product to configurable */
                        /**/
                        //$configProduct->getTypeInstance()->setUsedProductAttributeIds(array(92)); //attribute ID of attribute 'color' in my store

                        $configProduct->getTypeInstance()->setUsedProductAttributeIds($this->getConfigurableAttributesId($_p['configurableAttributesData'])); //attribute ID of attribute 'color' in my store
                        $configurableAttributesData = $configProduct->getTypeInstance()->getConfigurableAttributesAsArray();
                        $configProduct->setCanSaveConfigurableAttributes(true);
                        $configProduct->setConfigurableAttributesData($configurableAttributesData);

                        if (array_key_exists('simple', $_p)) {
                            $configurableProductsData = array();
                            foreach ($_p['simple'] as $simple) {

                                /*
                                $anamet = 'farve';
                                $attributeInfot = \Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($anamet)->getFirstItem();
                                $attributeIdt = $attributeInfot->getAttributeId();
                                $attributet = \Mage::getModel('catalog/resource_eav_attribute')->load($attributeIdt);
                                $attributeOptionst = $attributet->getSource()->getAllOptions(false);
                                */

                                echo '  trying to attach simple prod_id ' . $this->prodIds[$simple['id']] . PHP_EOL;


                                // farve
                                $farve_index = $this->getNewFarveId('farve', $simple['data']['farve']);
                                if (!is_null($farve_index)) {
                                    $configurableProductsData[$this->prodIds[$simple['id']]] = array( //['920'] = id of a simple product associated with this configurable
                                        '0' => array(
                                            'label' => $this->configurableOptionName, //attribute label
                                            'attribute_id' => $this->configurableAttributId, //attribute ID of attribute 'farve'
                                            //'value_index' =>  (string)$farve_index, //value for 'farve'
                                            'value_index' => $farve_index, //value for 'farve'
                                            'is_percent' => '0', //fixed/percent price for this option
                                            //'pricing_value' => '21' //value for the pricing
                                            'pricing_value' => null //value for the pricing
                                        )
                                    );
                                } else {
                                    echo '   farve is absent for the Simple ' . $this->prodIds[$simple['id']] . PHP_EOL;
                                }

                            }

                            $configProduct->setConfigurableProductsData($configurableProductsData);
                        }

                        // Selected attributes
                        $selAttrArray = [
                            'stik', 'kabinet', 'dimensioner', 'bluetooth1',
                            'pladespillerindgang', 'bluetooth_stereo', 'wifi_anslutning', 'airplay', 'spotifyconnect',
                            'fjernbetjening', 'trigger_12v', 'ethernet', 'niveau_kontrol', 'sw_lfe_janej'
                        ];
                        foreach ($selAttrArray as $selAttr) {
                            $attr_id = null;
                            if (isset($this->P[$selAttr])) {
                                foreach ($this->P[$selAttr] as $item) {
                                    if ($item['label'] == $_p['attr'][$selAttr]['value']) {
                                        $attr_id = $item['value'];
                                        break;
                                    }
                                }
                                $configProduct->setData($selAttr, $this->getNewFarveId($selAttr, $attr_id));
                            } else {
                                echo '   no data found for "' . $selAttr . '"' . PHP_EOL;
                            }
                        }

                        //flat type attributes
                        $selAttrArray = [
                            'ampbud_input_imedance', 'ampbus_input', 'ampbus_input_sensitivity', 'ampbus_output_impedance', 'ampbus_output_level', 'analoge_linjeindgange',
                            'analoge_linjeindgange', 'anbefaleteffekt', 'anmeldelser', 'atmos', 'automatisk_standby', 'basenhed',
                            'basenhed', 'basmellemtone', 'bassextension', 'batteri_janej_tid', 'biamp', 'biasvoltage',
                            'biasvoltage', 'biwiring', 'cable', 'cable_end', 'cd_drev', 'channel_imbalance',
                            'channel_imbalance', 'channel_imbalance_effekt', 'channel_separation', 'cjm_expecdate', 'cjm_hideshipdate', 'cjm_preorderdate',
                            'cjm_preorderdate', 'cjm_preordertext', 'cjm_ships_in', 'cjm_stockmessage', 'cjm_stocktext', 'compliance',
                            'compliance', 'country_of_manufacture', 'crossover', 'crosstalk', 'curve_accuracy', 'custom_design',
                            'custom_design', 'custom_design_from', 'custom_design_to', 'custom_layout_update', 'customtab', 'customtabtitle',
                            'customtabtitle', 'da_konverter_ja_nej', 'dac', 'dac_ic', 'damping_factor', 'dc_loop_resistance',
                            'dc_loop_resistance', 'densen_opgraderingsmuligheder', 'device', 'diameter', 'digitale_indgange', 'digitale_udgange',
                            'digitale_udgange', 'diskant', 'dispersion', 'distortion_elektrostat', 'driver_system', 'dynamic_range',
                            'dynamic_range', 'effekt', 'effekt_2ohm', 'effekt_6_ohm', 'effekt_power_handling', 'effekt4ohm',
                            'effekt4ohm', 'effekt8ohm', 'electrostaticcapacitance', 'enhed_bl', 'enhed_cms', 'enhed_effekt',
                            'enhed_effekt', 'enhed_effekt_max', 'enhed_electrical_xmax', 'enhed_fo', 'enhed_impedans', 'enhed_levc',
                            'enhed_levc', 'enhed_mmd', 'enhed_mms', 'enhed_no', 'enhed_qes', 'enhed_qms',
                            'enhed_qms', 'enhed_qts', 'enhed_revc', 'enhed_sd', 'enhed_splo', 'enhed_type',
                            'enhed_type', 'enhed_vas', 'enhed_xmax', 'factbox', 'fasejustering', 'foelsomhed',
                            'foelsomhed', 'format_cd', 'forstaerker_type', 'frekvensomraade', 'frekvensomraade_freq_response', 'freqency_response_phono_riaa',
                            'freqency_response_phono_riaa', 'frequency_response', 'frequency_response_line', 'gain', 'gui', 'has_options',
                            'has_options', 'hdam', 'hdmi', 'hdmi_info', 'hoejtaler_type_elektrostat', 'horetelefon_udgang',
                            'horetelefon_udgang', 'image_label', 'impedans', 'indgange', 'indput_sensitivity_phono', 'input_balanced_highlvl',
                            'input_balanced_highlvl', 'input_capacitance', 'input_impedance', 'input_impedance_linje', 'input_impedance_phono_inputs', 'input_sensitivity',
                            'input_sensitivity', 'input_sensitivity_highlvl', 'intermodulation_distortion', 'is_recurring', 'justering_crossover', 'kategori',
                            'kategori', 'kategori_sub', 'l_r_channel_diffrence', 'linearity', 'linje_utgang', 'load_impedance',
                            'load_impedance', 'losless_format', 'lossy_format', 'maal__sub', 'maal_forstaerker', 'manufacturer',
                            'manufacturer', 'materiale', 'max_indput_elektrostat', 'max_sampling_frequency', 'maxspl', 'mechanical_xmax',
                            'mechanical_xmax', 'microphone', 'midrange', 'monteringsmhul', 'motor', 'multiroom',
                            'multiroom', 'news_from_date', 'news_to_date', 'oevrigt', 'oplosning_bit', 'options_container',
                            'options_container', 'output_impedance', 'output_level', 'output_voltage', 'ovrigt_stereo', 'parallel_capacitance',
                            'parallel_capacitance', 'pc_delivery_cost', 'pc_delivery_time', 'pc_eanorupc_prod', 'pc_isbn_prod', 'pc_manufacturer_sku',
                            'pc_manufacturer_sku', 'pc_reitaler_message', 'peak_output_current', 'power', 'power_amplifier_section', 'power_consump',
                            'power_consump', 'power_consump_standby', 'poweroutput', 'pre_amplifier_section', 'pre_out', 'presets',
                            'presets', 'processor_loop', 'pwr_ctrl', 'pwr_tansformer', 'radio_bands', 'rated_max_power_output_thd',
                            'rated_max_power_output_thd', 'rca_output_impedance', 'rca_output_level', 'rec_cate_id', 'rec_cate_id_2', 'rec_cate_id_3',
                            'rec_cate_id_3', 'rec_cate_price_from', 'rec_cate_price_from_2', 'rec_cate_price_from_3', 'rec_cate_price_to', 'rec_cate_price_to_2',
                            'rec_cate_price_to_2', 'rec_cate_price_to_3', 'recurring_profile', 'required_options', 'reviewsfull', 'sampling_frequency',
                            'sampling_frequency', 'searchindex_weight', 'series_inductance', 'shelving_adj', 'short_description', 'shortparams',
                            'shortparams', 'signal_to_noise_ratio', 'signal_to_noise_ratio_line', 'signal_to_noise_ratio_phono_mc', 'signal_to_noise_ratio_phono_mm', 'slope_adj',
                            'slope_adj', 'special_from_date', 'special_price', 'special_to_date', 'speed', 'sps',
                            'sps', 'stn_ratio', 'stn_ratio_highlvl', 'stroemforsyning', 'stylus', 'sub_udgang',
                            'sub_udgang', 'sw_enhedstype', 'sw_indput_impedance', 'sw_indput_sensitivity', 'tape_output_impedance', 'tape_output_level',
                            'tape_output_level', 'terminaler', 'tier_price', 'tier_price_for_store', 'tilbudsskilt', 'tone_control',
                            'tone_control', 'total_harmonic_dist_line', 'total_harmonic_dist_phono', 'total_harmonic_distortion_thd', 'tracking_force', 'transient_intermodulation_dist',
                            'transient_intermodulation_dist', 'udgange', 'unit', 'usb_dsd', 'video_inputs', 'videobox',
                            'videobox', 'voice_coil'
                        ];
                        foreach ($selAttrArray as $selAttr) {
                            if (isset($_p['attr'][$selAttr]) && !is_null($_p['attr'][$selAttr]['value'])) {
                                $configProduct->setData($selAttr, $_p['attr'][$selAttr]['value']);
                            }
                        }


                        //load gallery images
                        if (isset($_p['media_gal'])) {
                            foreach ($_p['media_gal'] as $img) {
                                $img = $this->imgPath . $img;
                                if (file_exists($img)) {
                                    $configProduct->addImageToMediaGallery($img, 'image', false);
                                } else {
                                    echo '   image file not found for sku ' . $_p['sku'] . ' in ' . $img . PHP_EOL;
                                }
                            }
                        }


                        $configProduct->save();
                        $configProduct->unsetData();
                        echo ' successifully added ' . $this->prodIds[$simple['id']] . ' "' . $_p['name'] . '".' . PHP_EOL;
                    } catch (\Exception $e) {
                        //Mage::log($e->getMessage());
                        print_r( 'Error happens when array\'s item ' . $i . ' with id ' . $_p['id'] . PHP_EOL);
                        print_r($e->getMessage());
                    }

                }
            }
        }
        echo 'Finishing adding Configurable products' . PHP_EOL;
    }

    // function for testing purposes only. Can be safely removed
    protected function getAttrList()
    {
        $attributeSetCollection = \Mage::getResourceModel('eav/entity_attribute_set_collection')->load();
        foreach ($attributeSetCollection as $id=>$attributeSet) {
            $entityTypeId = $attributeSet->getEntityTypeId();
            $name = $attributeSet->getAttributeSetName();
            // Mage::log("ATTRIBUTE SET :".$name." - ".$id);
            print_r($id . ') id: ' . $entityTypeId . ', attribute set name : ' . $name . '<br />');

        }

        $attribute = \Mage::getSingleton('eav/config')
            ->getAttribute(\Mage_Catalog_Model_Product::ENTITY, $this->configurableOptionName);

        if ($attribute->usesSource()) {
            $configurableOptions = $attribute->getSource()->getAllOptions(false);
        }
        var_dump($attribute->getId());
    }

    public function getVisibilityId($visibility)
    {
        if ($visibility == 'Catalog, Search') return 4;
        if ($visibility == 'Not Visible Individually') return 1;
        if ($visibility == 'Catalog') return 2;
        if ($visibility == 'Search') return 3;

        return 1;
    }

    /**
     * @param array $data List of configurable attributes for configurable product
     * @return array $attributes array of configurable attributes id
     */
    public function getConfigurableAttributesId($data)
    {
        $attributes = Array();
        foreach ($data as $item) {
            $attribute = \Mage::getSingleton('eav/config')
                ->getAttribute(\Mage_Catalog_Model_Product::ENTITY, $item['attribute_code']);
            $attributes[] = $attribute->getId();
        }
        return $attributes;
    }

    public function getNewFarveId($arrayName, $oldId)
    {
        $farve_name = '';
        $newId = null;

        // getting farve name from old DB
        foreach ($this->P[$arrayName] as $item) {
            if ($item['value'] == $oldId) {
                $farve_name = $item['label'];
                break;
            }
        }

        //getting all values of 'farve' attribute
        $aname = $arrayName;
        $attributeInfo = \Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter($aname)->getFirstItem();
        if (isset($attributeInfo) && !is_null($attributeInfo)) {
            $attributeId = $attributeInfo->getAttributeId();
            $attribute = \Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
            $attributeOptions = $attribute->getSource()->getAllOptions(false);
            foreach ($attributeOptions as $item) {
                if ($item['label'] == $farve_name) {
                    $newId = $item['value'];
                    break;
                }
            }
        }
        return $newId;
    }

    protected function getProdData()
    {
        $h = fopen($this->dataFile, 'rb');
        $S = fread($h, filesize($this->dataFile));
        fclose($h);
        $this->P = unserialize($S);
    }

    public function initMage()
    {
        require_once $this->appMage;
        \Mage::setIsDeveloperMode(true);
        ini_set('display_errors', 1);
        umask(0);
        \Mage::app('admin');
        \Mage::register('isSecureArea', 1);
    }
}

new Boxup();