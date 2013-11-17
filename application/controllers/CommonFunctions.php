<?php

function getElement( $strInjection, $arrData ) {
        if( true == is_null( $strInjection ) || true == is_null( $arrData ) || false == is_array( $arrData ) ) return NULL;
        if( false == array_key_exists( $strInjection, $arrData ) ) return NULL;
        return $arrData[$strInjection];
}