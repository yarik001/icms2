<?php

class modelSubscriptions extends cmsModel {

    public function verifySubscription($confirm_token) {

        return $this->filterEqual('confirm_token', $confirm_token)->
                updateFiltered('subscriptions_bind', array(
                    'is_confirmed'  => 1,
                    'confirm_token' => null
                ));

    }

    public function getSubscriptionByToken($confirm_token) {
        return $this->filterEqual('confirm_token', $confirm_token)->getItem('subscriptions_bind');
    }

    public function deleteUserSubscriptions($user_id) {

        return $this->filterEqual('user_id', $user_id)->deleteFiltered('subscriptions_bind');

    }

    public function getSubscriptionItem($hash_or_id) {

        if(is_numeric($hash_or_id)){
            $this->filterEqual('id', $hash_or_id);
        } else {
            $this->filterEqual('hash', $hash_or_id);
        }

        return $this->getItem('subscriptions');

    }

    public function isSubscribed($target, $subscribe) {

        if(empty($target['hash'])){
            $target['hash'] = md5(serialize($target));
        }

        $list_item_id = $this->filterEqual('hash', $target['hash'])->getFieldFiltered('subscriptions', 'id');

        if(!$list_item_id){ return false; }

        if(!empty($subscribe['user_id'])){
            return $this->isUserSubscribed($subscribe['user_id'], $list_item_id);
        }

        return $this->isGuestSubscribed($subscribe['guest_email'], $list_item_id);

    }

    public function isUserSubscribed($user_id, $list_item_id) {

        $this->filterEqual('user_id', $user_id);
        $this->filterEqual('subscription_id', $list_item_id);

        return $this->getFieldFiltered('subscriptions_bind', 'id');

    }

    public function isGuestSubscribed($subscriber_email, $list_item_id) {

        $this->filterEqual('guest_email', $subscriber_email);
        $this->filterEqual('subscription_id', $list_item_id);

        return $this->getFieldFiltered('subscriptions_bind', 'id');

    }

    public function subscribe($target, $subscribe) {

        $is_now_create_list = false;

        if(empty($target['hash'])){
            $target['hash'] = md5(serialize($target));
        }

        // проверяем, нет ли такого списка
        $subscribe['subscription_id'] = $this->filterEqual('hash', $target['hash'])->getFieldFiltered('subscriptions', 'id');

        // создаём список
        if(!$subscribe['subscription_id']){

            $subscribe['subscription_id'] = $this->insert('subscriptions', $target, true);

            $is_now_create_list = $subscribe['subscription_id'];

        }

        $this->insert('subscriptions_bind', $subscribe);

        $this->reCountSubscribers($subscribe['subscription_id']);

        return $is_now_create_list;

    }

    public function reCountSubscribers($subscription_id) {

        $this->db->query("UPDATE {#}subscriptions SET subscribers_count=(SELECT COUNT(id) FROM {#}subscriptions_bind WHERE subscription_id = '{$subscription_id}' AND is_confirmed = 1) WHERE id = '{$subscription_id}'");

        return $this;

    }

}
