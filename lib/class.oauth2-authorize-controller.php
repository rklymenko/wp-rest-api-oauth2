<?php 
/**
 * @todo Add redirect uri check and validation
 * @todo 
 */
class OAuth2_Authorize_Controller extends OAuth2_Rest_Server {

  /**
   * State property
   * @var string
   */
  static public $state;

  // Validate Request
  static function validate ( WP_REST_Request $request ) {

    // Set state if provided
    self::setState( $request );

    // Check if the client ID and response type is set
    if ( ! isset( $request[ 'response_type' ] ) || ! isset( $request[ 'client_id' ] ) ) {
      $error = array(
        'error' => 'invalid_request',
        'error_description' => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.'
        );

      return new OAuth2_Response_Controller( $error );
    }
    
    // Check id client ID is valid.
    // We may be able to move this up in the first check as well
    if ( ! OAuth2_Storage_Controller::validateClient( $request[ 'client_id' ] ) ) {
      $error = array(
        'error' => 'invalid_request',
        'error_description' => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.'
        );

      return new OAuth2_Response_Controller( $error );
    }

    // Response type MUST be 'code'
    if ( ! self::validateResponseType( $request[ 'response_type' ] ) ) {
      $error = array(
        'error' => 'unsupported_response_type',
        'error_description' => 'The authorization server does not support obtaining an authorization code using this method.'
        );

      return new OAuth2_Response_Controller( $error );
    }

    $user_id = apply_filters( 'determine_current_user', false );

    if ( ! $user_id ) {
      global $wp;
      $current_url = add_query_arg( $wp->query_string . http_build_query( $request->get_params() ), '', site_url( $wp->request ) );
      wp_redirect( wp_login_url( $current_url ) ); 
      
      exit; 
    }

    // Setup the auth code and pass it back to the user
    print_r($user_id);
    exit;
    // If the user is logged in, begin the process of creating a auth code and returning it back to the user.

    // if we made it this far, everything has checked out and we can begin our logged in check and authorize process.
    $data = array( 
      'code' => '123123123' 
      );

    // If the state is not null, we need to return is as well
    if ( ! is_null( self::$state ) ) {
      $data[ 'state' ] = self::$state;
    }
    
    return new OAuth2_Response_Controller( $data );
  } 

  /**
   * Validates the response type
   *
   * According to OAuth2 Draft, the only supported response type for an auth code flow is code.
   *
   * @see  https://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-4.1.1
   * 
   * @param  [type] $response [description]
   * @return [type]           [description]
   */
  static public function validateResponseType( $response ) {
    if ( 'code' != $response ) {
      return false;
    }

    return true;
  }

  static public function setState ( $request ) {
    self::$state = isset( $request[ 'state' ] ) ? $request[ 'state' ] : null; 
  }





}