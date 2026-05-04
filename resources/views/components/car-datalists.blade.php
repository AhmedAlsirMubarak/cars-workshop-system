<datalist id="car-make-list">
    <option value="Toyota">
    <option value="Nissan">
    <option value="Honda">
    <option value="Mitsubishi">
    <option value="Mazda">
    <option value="Ford">
    <option value="Chevrolet">
    <option value="Hyundai">
    <option value="Kia">
    <option value="Lexus">
    <option value="BMW">
    <option value="Mercedes-Benz">
    <option value="Audi">
    <option value="Volkswagen">
    <option value="Jeep">
    <option value="Land Rover">
    <option value="Infiniti">
    <option value="GMC">
    <option value="Dodge">
    <option value="Ram">
    <option value="Cadillac">
    <option value="Porsche">
    <option value="Isuzu">
    <option value="Suzuki">
    <option value="Subaru">
    <option value="Haval">
    <option value="MG">
    <option value="Tesla">
    <option value="Volvo">
    <option value="Peugeot">
    <option value="Renault">
    <option value="Geely">
    <option value="Chery">
    <option value="BYD">
    <option value="Jaguar">
    <option value="Lincoln">
    <option value="Acura">
    <option value="Buick">
    <option value="Rolls-Royce">
    <option value="Bentley">
    <option value="Maserati">
    <option value="Alfa Romeo">
</datalist>

<datalist id="car-year-list">
    @for($y = date('Y') + 1; $y >= 1990; $y--)
    <option value="{{ $y }}">
    @endfor
</datalist>

<script>
window.CAR_MODELS = {
    'Toyota':        ['Camry','Corolla','Land Cruiser','Hilux','RAV4','Fortuner','Prado','Avalon','Yaris','Prius','Venza','Sequoia','Tundra','Tacoma','4Runner','Sienna','C-HR','Rush'],
    'Nissan':        ['Patrol','Altima','Maxima','Sentra','Pathfinder','Murano','X-Trail','Kicks','Navara','Juke','Note','Sunny','Tiida','Armada','Frontier','GT-R'],
    'Honda':         ['Civic','Accord','CR-V','Pilot','HR-V','Odyssey','Jazz','City','BR-V','ZR-V','Passport'],
    'Mitsubishi':    ['Pajero','Outlander','Eclipse Cross','ASX','L200','Montero','Lancer','Galant'],
    'Mazda':         ['Mazda 3','Mazda 6','CX-5','CX-9','CX-3','CX-30','BT-50','MX-5'],
    'Ford':          ['F-150','Explorer','Expedition','Edge','Mustang','Ranger','Fusion','Focus','Escape','Bronco','Maverick'],
    'Chevrolet':     ['Silverado','Tahoe','Suburban','Equinox','Traverse','Blazer','Malibu','Spark','Camaro','Colorado','Trailblazer'],
    'Hyundai':       ['Tucson','Santa Fe','Elantra','Sonata','Accent','Creta','Palisade','Kona','i10','i20','i30','Staria'],
    'Kia':           ['Sportage','Sorento','Telluride','Carnival','Seltos','Stinger','Rio','Cerato','K5','EV6'],
    'Lexus':         ['LX','GX','RX','NX','ES','GS','IS','LS','UX','LC'],
    'BMW':           ['1 Series','2 Series','3 Series','4 Series','5 Series','7 Series','X1','X3','X5','X7','M3','M5','Z4'],
    'Mercedes-Benz': ['A-Class','C-Class','E-Class','S-Class','GLA','GLC','GLE','GLS','G-Class','CLA','AMG GT'],
    'Audi':          ['A3','A4','A6','A8','Q3','Q5','Q7','Q8','TT','R8','e-tron'],
    'Volkswagen':    ['Golf','Passat','Tiguan','Touareg','Polo','Arteon','Atlas','T-Roc','ID.4'],
    'Jeep':          ['Wrangler','Grand Cherokee','Cherokee','Compass','Renegade','Gladiator','Grand Wagoneer'],
    'Land Rover':    ['Range Rover','Range Rover Sport','Range Rover Velar','Range Rover Evoque','Defender','Discovery','Discovery Sport'],
    'Infiniti':      ['QX80','QX60','QX55','QX50','Q50','Q60'],
    'GMC':           ['Sierra','Yukon','Acadia','Canyon','Terrain','Envoy'],
    'Dodge':         ['Durango','Challenger','Charger','Journey'],
    'Ram':           ['1500','2500','3500','ProMaster'],
    'Cadillac':      ['Escalade','CT5','CT4','XT5','XT6','XT4'],
    'Porsche':       ['Cayenne','Macan','Panamera','911','Taycan','Boxster','Cayman'],
    'Isuzu':         ['D-Max','MU-X','Trooper','NPR'],
    'Suzuki':        ['Swift','Jimny','Vitara','Grand Vitara','Alto','Celerio','S-Presso'],
    'Subaru':        ['Forester','Outback','XV','Impreza','Legacy','WRX','BRZ','Ascent'],
    'Haval':         ['H6','H9','Jolion','F7','H2','Dargo'],
    'MG':            ['MG3','MG5','MG6','ZS','HS','ZST','RX5'],
    'Tesla':         ['Model 3','Model S','Model X','Model Y','Cybertruck'],
    'Volvo':         ['XC40','XC60','XC90','S60','S90','V60','V90'],
    'Peugeot':       ['108','208','308','508','2008','3008','5008','Rifter'],
    'Renault':       ['Clio','Megane','Captur','Duster','Koleos','Sandero','Zoe'],
    'Geely':         ['Emgrand','Coolray','Tugella','Atlas','Okavango'],
    'Chery':         ['Tiggo 4','Tiggo 7','Tiggo 8','Arrizo 5','Arrizo 6'],
    'BYD':           ['Atto 3','Han','Tang','Song','Seal','Dolphin','Sealion'],
    'Jaguar':        ['XE','XF','XJ','F-Pace','E-Pace','I-Pace','F-Type'],
    'Lincoln':       ['Navigator','Aviator','Corsair','Nautilus'],
    'Acura':         ['MDX','RDX','TLX','NSX','Integra'],
    'Buick':         ['Enclave','Envision','Encore','LaCrosse'],
    'Rolls-Royce':   ['Phantom','Ghost','Wraith','Dawn','Cullinan'],
    'Bentley':       ['Bentayga','Continental GT','Flying Spur','Mulsanne'],
    'Maserati':      ['Ghibli','Quattroporte','Levante','GranTurismo','Grecale'],
    'Alfa Romeo':    ['Giulia','Stelvio','Giulietta','Tonale'],
};
</script>
