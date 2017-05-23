<?php
namespace Excellence\Sortby\Block\Rewrite\Customer\Widget;

class Dob extends \Magento\Customer\Block\Widget\Dob
{
    /**
     * Create correct date field
     *
     * @return string
     */
    public function getFieldHtml()
    {
        $this->dateElement->setData([
            'extra_params' => $this->isRequired() ? 'data-validate="{required:true}"' : '',
            'name' => $this->getHtmlId(),
            'id' => $this->getHtmlId(),
            'class' => $this->getHtmlClass(),
            'value' => $this->getValue(),
            'date_format' => $this->getDateFormat(),
            'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            'years_range' => '-120y:c+nn',
            'max_date' => '-1d',
            'change_month' => 'true',
            'change_year' => 'true',
            'show_on' => 'both',
            'readonly' => 'true'
        ]);
        return $this->dateElement->getHtml();
    }
}
