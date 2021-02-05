<?php


namespace OlegKravets\LaravelRedisService\Traits;


use Exception;
use OlegKravets\LaravelRedisService\Mutators\QueryBuilderMutator;
use OlegKravets\LaravelRedisService\ServiceHandlers\OutboundStream;

trait UsesRedisService
{
    /**
     * This mechanism will prevent
     *
     * @return QueryBuilderMutator
     * @throws Exception
     */
    protected function newBaseQueryBuilder(): QueryBuilderMutator
    {
        $conn = $this->getConnection();
        $grammar = $conn->getQueryGrammar();
        $builder = new QueryBuilderMutator($conn, $grammar, $conn->getPostProcessor());
        if (!isset($this->_service_channel)) {
            throw new Exception("Model does not contain service channel");
        }
        $builder->model=$this->toArray();
        $builder->_service_channel = $this->_service_channel;
        $builder->service_connection = new OutboundStream();
        return $builder;
    }
}
