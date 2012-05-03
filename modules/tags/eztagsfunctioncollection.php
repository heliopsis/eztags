<?php

/**
 * eZTagsFunctionCollection class implements fetch functions for eztags
 *
 */
class eZTagsFunctionCollection
{
    /**
     * Fetches eZTagsObject object for the provided tag ID
     *
     * @static
     * @param integer $tag_id
     * @return array
     */
    static public function fetchTag( $tag_id, $remote_id = false )
    {
    	$eztagsINI = eZINI::instance( 'eztags.ini' );
    	$showHidden = $eztagsINI->variable( 'VisibilitySettings', 'ShowHiddenTags' ) === 'enabled';

    	if ( $tag_id === false && $remote_id !== false )
        {
            $tag = eZTagsObject::fetchByRemoteID( $remote_id );
        }
        else
        {
            $tag = eZTagsObject::fetch( $tag_id );
        }

        if ( $tag === null )
        {
            $result = array( 'error' => array( 'error_type' => 'kernel',
                                               'error_code' => eZError::KERNEL_NOT_FOUND ) );
        }
        else
        {
        	if( $tag instanceof eZTagsObject && ( $showHidden || $result->isVisible() ) )
        	{
        		$result = array( 'result' => $tag );
        	}
        	else
        	{
        		$result = array( 'result' => false );
        	}
        }

        return $result;
    }

    /**
     * Fetches all tags named with provided keyword
     *
     * @static
     * @param string $keyword
     * @return array
     */
    static public function fetchTagsByKeyword( $keyword )
    {
        $eztagsINI = eZINI::instance( 'eztags.ini' );
        $showHidden = $eztagsINI->variable( 'VisibilitySettings', 'ShowHiddenTags' ) === 'enabled';

        $result = eZTagsObject::fetchByKeyword( $keyword, $showHidden );

        if( is_array( $result ) && !empty( $result ) )
            return array( 'result' => $result );
        else
            return array( 'result' => false );
    }

    /**
     * Fetches subtree of tags by specified parameters
     *
     * @static
     * @param integer $parentTagID
     * @param array $sortBy
     * @param integer $offset
     * @param integer $limit
     * @param integer $depth
     * @param string $depthOperator
     * @param bool $includeSynonyms
     * @return array
     */
    static public function fetchTagTree( $parentTagID, $sortBy, $offset, $limit, $depth, $depthOperator, $includeSynonyms )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => false );

        $eztagsINI = eZINI::instance( 'eztags.ini' );
        $showHidden = $eztagsINI->variable( 'VisibilitySettings', 'ShowHiddenTags' ) === 'enabled';

        $params = array( 'SortBy' => $sortBy,
                         'Offset' => $offset,
                         'Limit'  => $limit,
                         'IncludeSynonyms' => $includeSynonyms,
                         'ShowHidden' => $showHidden );

        if ( $depth !== false )
        {
            $params['Depth'] = $depth;
            $params['DepthOperator'] = $depthOperator;
        }

        $tags = eZTagsObject::subTreeByTagID( $params, $parentTagID );

        return array( 'result' => $tags );
    }

    /**
     * Fetches subtree tag count by specified parameters
     *
     * @static
     * @param integer $parentTagID
     * @param integer $depth
     * @param string $depthOperator
     * @param bool $includeSynonyms
     * @return integer
     */
    static public function fetchTagTreeCount( $parentTagID, $depth, $depthOperator, $includeSynonyms )
    {
        if ( !is_numeric( $parentTagID ) || (int) $parentTagID < 0 )
            return array( 'result' => 0 );

        $eztagsINI = eZINI::instance( 'eztags.ini' );
        $showHidden = $eztagsINI->variable( 'VisibilitySettings', 'ShowHiddenTags' ) === 'enabled';
        $params = array( 'IncludeSynonyms' => $includeSynonyms,
                         'ShowHidden' => $showHidden );

        if ( $depth !== false )
        {
            $params['Depth'] = $depth;
            $params['DepthOperator'] = $depthOperator;
        }

        $tagsCount = eZTagsObject::subTreeCountByTagID( $params, $parentTagID );

        return array( 'result' => $tagsCount );
    }

    /**
     * Fetches latest modified tags by specified parameters
     *
     * @static
     * @param integer $parentTagID
     * @param integer $limit
     * @return array
     */
    static public function fetchLatestTags( $parentTagID = false, $limit = 0 )
    {
        $filterArray = array( 'main_tag_id' => 0 );

        if ( $parentTagID !== false )
            $filterArray['parent_id'] = (int) $parentTagID;

        $eztagsINI = eZINI::instance( 'eztags.ini' );
        $showHidden = $eztagsINI->variable( 'VisibilitySettings', 'ShowHiddenTags' ) === 'enabled';
        if( !$showHidden )
            $filterArray['hidden'] = 0;

        $result = eZPersistentObject::fetchObjectList( eZTagsObject::definition(), null,
                                                       $filterArray,
                                                       array( 'modified' => 'desc' ),
                                                       array( 'offset' => 0, 'limit' => $limit ) );

        if ( is_array( $result ) && !empty( $result ) )
            return array( 'result' => $result );
        else
            return array( 'result' => false );
    }
}

?>
