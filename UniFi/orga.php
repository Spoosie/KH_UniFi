<?php

function CreateCategory( $Name, $Ident = '', $ParentID = 0 )
{
	global $RootCategoryID;

	echo "CreateCategory: ( $Name, $Ident, $ParentID ) \n";

	if ( '' != $Ident )
	{
		$CatID = @IPS_GetObjectIDByIdent( $Ident, $ParentID );
		if ( false !== $CatID )
		{
		   $Obj = IPS_GetObject( $CatID );
		   if ( 0 == $Obj['ObjectType'] ) // is category?
		      return $CatID;
		}
	}
	$CatID = IPS_CreateCategory();
	IPS_SetName( $CatID, $Name );
   IPS_SetIdent( $CatID, $Ident );

	if ( 0 == $ParentID )
		if ( IPS_ObjectExists( $RootCategoryID ) )
			$ParentID = $RootCategoryID;
	IPS_SetParent( $CatID, $ParentID );

	return $CatID;
}

function SetVariable( $VarID, $Type, $Value )
{
	switch( $Type )
	{
	   case 0: // boolean
	      SetValueBoolean( $VarID, $Value );
	      break;
	   case 1: // integer
	      SetValueInteger( $VarID, $Value );
	      break;
	   case 2: // float
	      SetValueFloat( $VarID, $Value );
	      break;
	   case 3: // string
	      SetValueString( $VarID, $Value );
	      break;
	}
}
function CreateVariable( $Name, $Type, $Value, $Ident = '', $ParentID = 0 )
{
	echo "CreateVariable: ( $Name, $Type, $Value, $Ident, $ParentID ) \n";
	if ( '' != $Ident )
	{
		$VarID = @IPS_GetObjectIDByIdent( $Ident, $ParentID );
		if ( false !== $VarID )
		{
		   SetVariable( $VarID, $Type, $Value );
		   return;
		}
	}
	$VarID = @IPS_GetObjectIDByName( $Name, $ParentID );
	if ( false !== $VarID ) // exists?
	{
	   $Obj = IPS_GetObject( $VarID );
	   if ( 2 == $Obj['ObjectType'] ) // is variable?
		{
		   $Var = IPS_GetVariable( $VarID );
		   if ( $Type == $Var['VariableValue']['ValueType'] )
			{
			   SetVariable( $VarID, $Type, $Value );
			   return;
			}
		}
	}
	$VarID = IPS_CreateVariable( $Type );
	IPS_SetParent( $VarID, $ParentID );
	IPS_SetName( $VarID, $Name );
	if ( '' != $Ident )
	   IPS_SetIdent( $VarID, $Ident );
	SetVariable( $VarID, $Type, $Value );
}

?>