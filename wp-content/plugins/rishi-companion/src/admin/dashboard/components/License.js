import { css } from '@emotion/css';
import styled from "@emotion/styled";
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from "@wordpress/i18n";
import Button from "../../ui-components/Button";
import Icon from "../../ui-components/Icon";
import LicenseUI from "../../ui-components/LicenseUI";
import { applyFilters } from "@wordpress/hooks";

const LicenseValidation = styled.div`
		padding: .5rem;
		border: 1px solid #D0D5DD;
		border-radius: 6px;
		border-left-width: 4px;
		&.error-msg {
			border-left-color: #d92e21;
		}

		&.success-msg {
			border-left-color: #4ade80;
		}
	`
const License = ({toast}) => {
	const [licenseKey, setLicenseKey] = useState('');
	const [isAPIJOB, setIsAPIJOB] = useState(false);
	const [savedLicense, setSavedLicense] = useState(false);
	const [licenseStatus, setLicenseStatus] = useState({
		status: "",
		message: ""
	});

	const [eyeIcon, setEyeIcon] = useState('eye')
	const passwordRef = useRef(null)

	const activateLicense = (e) => {
		e.preventDefault()
		setIsAPIJOB(true);

		fetch(
			`${RishiCompanionDashboard.ajaxUrl}?action=rishi_companion_control_activate_license&security=${RishiCompanionDashboard.LicenseControlsNonce}&rishiCompLicenseKey=${licenseKey}`,
			{
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				method: 'POST',
				body: JSON.stringify({
					security: RishiCompanionDashboard.LicenseControlsNonce,
					licenseKey: licenseKey
				}),
			}
		)
			.then((r) => r.json())
			.then((r) => {
				if (r.success) {
					'' != r.data.license_key ? setSavedLicense(true) : setSavedLicense(false);
					setLicenseKey(r.data.license);
					setLicenseStatus(r.data.status);
                    toast.success(__('License activation completed successfully.', 'rishi-companion'))
					setSavedLicense(true);
				} else if (!r.success) {
					setLicenseStatus(r.data.status);
					toast.error(__('Error: License activation failed.', 'rishi-companion'))
					setSavedLicense(true);
				}
				setIsAPIJOB(false);
			})
		if (passwordRef.current) {
			const input = passwordRef.current;
			input.type = 'password'
			setEyeIcon('eye');
		}
	}

	const deActivateLicense = (e) => {
		e.preventDefault()
		setIsAPIJOB(true);
		fetch(
			`${RishiCompanionDashboard.ajaxUrl}?action=rishi_companion_control_deactivate_license`,
			{
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				method: 'POST',
			}
		)
			.then((r) => r.json())
			.then((r) => {
				if (r.success) {
					setLicenseKey(r.data.license);
					setLicenseStatus(r.data.status);
                    toast.success(__('License deactivation completed successfully.', 'rishi-companion'))
				}
				setIsAPIJOB(false);
			})
	}

	const handleResetLicense = (event) => {
        event.preventDefault();
        setIsAPIJOB(true);
        fetch(
            `${RishiCompanionDashboard.ajaxUrl}?action=rishi_companion_reset_license_status`,
            {
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                method: 'POST',
            }
        )
            .then((r) => {
                setSavedLicense(false);
                setLicenseStatus({ status: "", message: "" })
                setLicenseKey('')
                setIsAPIJOB(false);
                toast.success(__('Reset successful.', 'rishi-companion'))
            })
    };

	useEffect(() => {
		fetch(
			`${RishiCompanionDashboard.ajaxUrl}?action=rishi_companion_get_license_status`,
			{
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				method: 'POST',
			}
		)
			.then((r) => r.json())
			.then((r) => {
				setLicenseKey(r.data.license)
				setLicenseStatus(r.data.status);
				if ('' !== r.data.license) {
					setSavedLicense(true);
				}
			})
	}, [savedLicense]);

	const handleLicenseVisibility = () => {
		if (passwordRef.current) {
			const input = passwordRef.current;
			input.type = input.type === 'password' ? 'text' : 'password';
			input.type === 'password' ? setEyeIcon('eye') : setEyeIcon('eye-cross');
		}
	}

	const ProLicenseFilter = RishiCompanionDashboard.proActivated && applyFilters('rishi_pro_license', { icon: Icon, button: Button, toast:toast })

	return (
		<>
			<div className="rishi-main-wrapper">
				<LicenseUI>
					<div
						className={css`
							border: 1px solid #EAECF0;
							border-radius: 12px;
							padding: 24px;
							max-width: 665px;
						`}
					>
						<div>
							<h2>
								{__("Activate Rishi Companion License", "rishi-companion")}
							</h2>
						</div>
						<div
							className={css`
							& > * + * {
								margin-top: 14px;
								max-width: 620px;
							}
						`}
						>
							<div className={css`
							& p {
								font-style: italic;
								color: #2d3039;
							}
						`}>
								{<p dangerouslySetInnerHTML={{ __html: licenseStatus.message }}></p>}
							</div>
							<form action="get">
								<div
									className={css`
									position: relative;
								`}
								>
									<span className={css`
									position: absolute;
									height: 100%;
									width: 2.75rem;
									display: flex;
									justify-content: center;
									align-items: center;
									right: 0;
								`}>
										<input
											type="checkbox"
											className={css`display:none !important;`}
											name="license_visibility"
											id="license_visibility"
											onChange={handleLicenseVisibility}
										/>
										<label
											htmlFor="license_visibility"
											className={css`
											display:flex;
											align-items: center;
											transition: color 0.2s ease-in-out;
											&:hover {
												color: #2d3039;
											}
											${licenseStatus.status === "valid" ? 'pointer-events: none;' : ''}
										`}
										>
											<Icon icon={eyeIcon} />
										</label>
									</span>

									<input
										autoComplete="off"
										defaultValue={licenseKey}
										type="password"
										required
										ref={passwordRef}
										disabled={isAPIJOB || licenseStatus.status === "valid"}
										onChange={(event) => { setLicenseKey(event.target.value); }}
										placeholder={__("License Key", "rishi-companion")}
										className={css`
										width: 100%;
										padding: 10px 2.7rem  10px 1rem !important;
										line-height: 1.5 !important;
										border: 1px solid #D0D5DD !important;
									`}
									/>

								</div>

							</form>
							{
								savedLicense && licenseKey && "valid" === licenseStatus.status &&
								<LicenseValidation className="success-msg">
									<p>
										{__("Your license key for Rishi Companion is activated on the site.", "rishi-companion")}
									</p>
								</LicenseValidation>
							}
							{savedLicense && licenseKey && "valid" !== licenseStatus.status &&
								<LicenseValidation className="error-msg">
									<p>
										{__("Your license key for Rishi Companion is not activated on the site yet, Please Activate.", "rishi-companion")}
									</p>
								</LicenseValidation>
							}

							<>
								{
									savedLicense && licenseKey && "valid" === licenseStatus.status &&
									<Button
										onClick={(e) => deActivateLicense(e)}
										type="submit"
										disabled={isAPIJOB}
										isLoading={isAPIJOB}
									>
										{__("Deactivate License", "rishi-companion")}
									</Button>
								}
								{
									"valid" !== licenseStatus.status &&
									<div className={css`display: flex;
													align-items: center;
													gap: 16px;`}>
										<Button
											type="submit"
											onClick={(e) => activateLicense(e)}
											disabled={isAPIJOB}
											isLoading={isAPIJOB}
										>
											{__("Activate License", "rishi-companion")}
										</Button>
										{
											savedLicense &&
											<div className="reset-license">
												<a href="#" onClick={handleResetLicense} >{__('Reset License', 'rishi-companion')}</a>
											</div>
										}
								</div>
								}
							</>
							<span
								className={css`
									margin-top: 15px;
									font-size: 14px;
									font-style: italic;
									display: block;
								`}
							>
								{__("Note: You can get the free license for Rishi Companion ", "rishi-companion")}<a target="_blank" href="https://rishitheme.com/rishi-companion/">{__("here", "rishi-companion")}</a>.
							</span>
						</div>
					</div>
					{ProLicenseFilter}
				</LicenseUI>
			</div>
		</>
	)
}
export default License
