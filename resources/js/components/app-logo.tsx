export default function AppLogo() {
    return (
        <>
            <div className="flex h-10 w-10 items-center justify-center">
                <img
                    src="/logo_costeno_LP.svg"
                    alt="Costeño LP"
                    className="h-8 w-8 object-contain"
                />
            </div>
            <div className="ml-1 grid flex-1 text-left">
                <img
                    src="/logo_costeno_LP.svg"
                    alt="Costeño LP"
                    className="h-6 w-auto object-contain"
                />
            </div>
        </>
    );
}
