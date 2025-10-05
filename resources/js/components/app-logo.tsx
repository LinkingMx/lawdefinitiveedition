import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div
                className="flex aspect-square items-center justify-center"
                style={{ width: '2.4rem', height: '2.4rem' }}
            >
                <AppLogoIcon className="h-full w-full text-[#897053]" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    Costeño LP
                </span>
            </div>
        </>
    );
}
