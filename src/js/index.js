/**
 * External dependencies
 */
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { PanelBody, TextControl, Button, Notice } from '@wordpress/components';
import { registerPlugin } from '@wordpress/plugins';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
// eslint-disable-next-line import/no-extraneous-dependencies
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
// eslint-disable-next-line import/no-extraneous-dependencies
import { faRobot } from '@fortawesome/free-solid-svg-icons';

const ApiKeyForm = ( {
	apiKey,
	setApiKey,
	isValidating,
	handleSaveKey,
	validationMessage,
	isValid,
} ) => {
	return (
		<>
			<h3>{ __( 'OpenAI API Key', 'gutenberg-ai-migration' ) }</h3>
			<p>
				{ __(
					'Enter your OpenAI API key to get started with AI migration features.',
					'gutenberg-ai-migration'
				) }
			</p>
			<TextControl
				label={ __( 'API Key', 'gutenberg-ai-migration' ) }
				value={ apiKey }
				onChange={ setApiKey }
				type="password"
				help={ __(
					'Your API key will be stored securely and used for AI operations.',
					'gutenberg-ai-migration'
				) }
			/>
			<Button
				variant="primary"
				onClick={ handleSaveKey }
				isBusy={ isValidating }
				disabled={ ! apiKey.trim() || isValidating }
			>
				{ isValidating
					? __( 'Validating…', 'gutenberg-ai-migration' )
					: __( 'Save & Validate Key', 'gutenberg-ai-migration' ) }
			</Button>
			{ validationMessage && (
				<Notice
					status={ isValid ? 'success' : 'error' }
					isDismissible={ false }
				>
					{ validationMessage }
				</Notice>
			) }
		</>
	);
};

const SettingsPanel = ( { onEditKey } ) => {
	return (
		<>
			<h3>{ __( 'AI Migration Settings', 'gutenberg-ai-migration' ) }</h3>

			{ /* Additional options section at the top */ }
			<div style={ { marginBottom: '30px' } }>
				<h4>{ __( 'Migration Options', 'gutenberg-ai-migration' ) }</h4>
				<p>
					{ __(
						'Configure your AI migration preferences below.',
						'gutenberg-ai-migration'
					) }
				</p>

				{ /* Placeholder for future migration options */ }
				<div
					style={ {
						padding: '15px',
						border: '1px dashed #ccc',
						borderRadius: '4px',
						backgroundColor: '#f9f9f9',
						marginTop: '10px',
					} }
				>
					<p
						style={ {
							margin: 0,
							fontStyle: 'italic',
							color: '#666',
						} }
					>
						{ __(
							'Migration options will be added here (e.g., model selection, temperature settings, etc.)',
							'gutenberg-ai-migration'
						) }
					</p>
				</div>
			</div>

			{ /* API Key status and edit section at the bottom */ }
			<div
				style={ {
					borderTop: '1px solid #ddd',
					paddingTop: '20px',
					marginTop: '20px',
				} }
			>
				<p>
					{ __(
						'Your OpenAI API key is configured and ready to use.',
						'gutenberg-ai-migration'
					) }
				</p>
				<Notice status="success" isDismissible={ false }>
					{ __(
						'API key is valid and saved!',
						'gutenberg-ai-migration'
					) }
				</Notice>
				<Button
					variant="secondary"
					onClick={ onEditKey }
					style={ { marginTop: '10px' } }
				>
					{ __( 'Edit OpenAI API Key', 'gutenberg-ai-migration' ) }
				</Button>
			</div>
		</>
	);
};

const GutenbergAIMigrationPluginArea = () => {
	const [ apiKey, setApiKey ] = useState( '' );
	const [ isValidating, setIsValidating ] = useState( false );
	const [ isValid, setIsValid ] = useState( false );
	const [ validationMessage, setValidationMessage ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );

	// Load saved API key on component mount
	useEffect( () => {
		loadApiKey();
	}, [] );

	const loadApiKey = async () => {
		try {
			const response = await apiFetch( {
				path: '/gutenberg-ai-migration/v1/api-key',
				method: 'GET',
			} );
			if ( response.success && response.data.apiKey ) {
				setApiKey( response.data.apiKey );
				setIsValid( true );
			}
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error loading API key:', error );
		} finally {
			setIsLoading( false );
		}
	};

	const validateApiKey = async ( key ) => {
		setIsValidating( true );
		setValidationMessage( '' );

		try {
			const response = await apiFetch( {
				path: '/gutenberg-ai-migration/v1/validate-key',
				method: 'POST',
				data: { apiKey: key },
			} );

			if ( response.success ) {
				setIsValid( true );
				setValidationMessage(
					__( 'API key is valid!', 'gutenberg-ai-migration' )
				);
				// Save the key
				await saveApiKey( key );
			} else {
				setIsValid( false );
				setValidationMessage(
					response.message ||
						__( 'Invalid API key', 'gutenberg-ai-migration' )
				);
			}
		} catch ( error ) {
			setIsValid( false );
			setValidationMessage(
				__( 'Error validating API key', 'gutenberg-ai-migration' )
			);
			// eslint-disable-next-line no-console
			console.error( 'Validation error:', error );
		} finally {
			setIsValidating( false );
		}
	};

	const saveApiKey = async ( key ) => {
		try {
			await apiFetch( {
				path: '/gutenberg-ai-migration/v1/api-key',
				method: 'POST',
				data: { apiKey: key },
			} );
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error saving API key:', error );
		}
	};

	const handleSaveKey = () => {
		if ( apiKey.trim() ) {
			validateApiKey( apiKey.trim() );
		}
	};

	const handleEditKey = () => {
		setIsValid( false );
		setApiKey( '' );
		setValidationMessage( '' );
	};

	const renderPanelContent = () => {
		if ( isLoading ) {
			return <p>{ __( 'Loading…', 'gutenberg-ai-migration' ) }</p>;
		}

		if ( isValid ) {
			return <SettingsPanel onEditKey={ handleEditKey } />;
		}

		return (
			<ApiKeyForm
				apiKey={ apiKey }
				setApiKey={ setApiKey }
				isValidating={ isValidating }
				handleSaveKey={ handleSaveKey }
				validationMessage={ validationMessage }
				isValid={ isValid }
			/>
		);
	};

	return (
		<>
			<PluginSidebarMoreMenuItem
				target="gutenberg-ai-migration-sidebar"
				icon={ <FontAwesomeIcon icon={ faRobot } /> }
			>
				{ __( 'Gutenberg AI Migration', 'gutenberg-ai-migration' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="gutenberg-ai-migration-sidebar"
				icon={ <FontAwesomeIcon icon={ faRobot } /> }
				title={ __(
					'Gutenberg AI Migration',
					'gutenberg-ai-migration'
				) }
			>
				<PanelBody>{ renderPanelContent() }</PanelBody>
			</PluginSidebar>
		</>
	);
};

registerPlugin( 'gutenberg-ai-migration-plugin-area', {
	render: GutenbergAIMigrationPluginArea,
} );
