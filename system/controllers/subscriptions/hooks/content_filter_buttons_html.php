<?php

class onSubscriptionsContentFilterButtonsHtml extends cmsAction {

    public function run($data){

        list($ctype_name, $form_url, $filters) = $data;

        $params = array(
            'field_filters' => $filters,
            'filters' => array()
        );

        $cat = cmsModel::getCachedResult('current_ctype_category');

        if(!empty($cat['id'])){
            $params['filters'][] = array(
                'field'     => 'category_id',
                'condition' => 'eq',
                'value'     => $cat['id']
            );
        }

        $current_dataset = cmsModel::getCachedResult('current_ctype_dataset');

        if(!empty($current_dataset['filters'])){
            foreach($current_dataset['filters'] as $filter){

                if (!isset($filter['value'])) { continue; }
                if (($filter['value'] === '') && !in_array($filter['condition'], array('nn', 'ni'))) { continue; }
                if (empty($filter['condition'])) { continue; }

                if ($filter['value'] !== '') { $filter['value'] = string_replace_user_properties($filter['value']); }

                $params['filters'][] = $filter;

            }
        }

        return $this->renderSubscribeButton(array(
            'controller' => 'content',
            'subject'    => $ctype_name,
            'params'     => $params
        ));

    }

}
