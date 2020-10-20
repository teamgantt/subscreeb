<?php

namespace TeamGantt\Subscreeb\Gateways\Braintree\Gateway\Instrumented;

trait LogsMethods
{
    /**
     * Instrument a function to log before and after
     * @param string $functionName 
     * @param mixed $args 
     * @return mixed 
     */
    public function instrumented($functionName, ...$args)
    {
        $callable = ['parent', $functionName];
        $class = get_class($this);
        $id = $class . '::' . $functionName;

        $this->logger->info("Start " . $id, $args);
        $result = call_user_func_array($callable, $args);
        $this->logger->info("Stop " . $id, ['result' => $result]);

        return $result;
    }
}
