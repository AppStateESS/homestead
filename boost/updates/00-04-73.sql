alter table hms_room add column persistent_id character varying;

create table hms_damage_type(
    id          integer not null,
    category    character varying NOT NULL,
    description character varying NOT NULL,
    cost        integer,
    PRIMARY KEY(id)
);

create sequence hms_damage_type_seq;

create table hms_room_damage(
    id                  integer not null,
    room_persistent_id  character varying not null,
    term                integer not null REFERENCES hms_term(term),
    damage_type         integer not null REFERENCES hms_damage_type(id),
    note                character varying,
    repaired            smallint not null default 0,
    reported_by         character varying not null,
    reported_on         integer not null,
    PRIMARY KEY(id)
);

create sequence hms_room_damage_seq;

INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Light Cover- Cracked/Missing',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Stars/tape residue covering 25% or less ',35);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Stars/tape residue covering 25-50% ',70);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Stars/tape residuecovering more than 50%',.00);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Tear or Scratches covering 25% or less ',35);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Tear or Scratches covering 25-50%',70);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Tear or Scratches covering more than 50%',.00);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Ceiling','Tile Replacement',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Appalachian Heights Apartment',125);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Bathroom/Shower (suite-style buildings)',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Carpet-Residence Hall',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Carpet-Suite Apartment',80);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Furniture – (Dusting)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Microfridge',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Student Moving a Microfridge',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Microwave',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Oven',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Refrigerator',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Residence Hall Room',50);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Sink (kitchen or bathroom)',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Tile Floor',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Vacuum',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Housekeeping Labor Charges (per hour)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Cleaning','Winkler Suite',175);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Closet','Door – broken/split/missing',125);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Closet','Door – remount',32);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Closet','Paint (per side)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Hinges – damaged or disassembled',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Nails / Nail Holes covering less than 25%',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Nails / Nail Holes covering more than 25%',150);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Peep Hole – missing / damaged',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Replace – broken / scratches / writing',150);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Scratches covering less than 25%',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Scratches covering more than 25%',70);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Stop – Missing / Broken',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Closer - Put together',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Closer - Missing',50);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Tape Residue (per side)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Door','Cleaning Writing on Door',50);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Floors','Carpet – holes / replacement',.00);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Floors','Tile – missing / damaged (per tile)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Air Conditioning Unit – Missing Knob',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Bar Stool – Broken Rung (per rung)',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Bar Stool – Replace',110);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Bed – Damaged Frame',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Bed – Reassemble Frame',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Bed – Replace Frame',145);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Bed – Un-bunking',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Cabinet – Damaged',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Chair – Cracked',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Chair – Refinish (Suite-Style Buildings)',50);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Chair – Replace (Suite-Style Buildings)',135);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Chair – Replace (Traditional Building)',135);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Chair – Small Hole',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Desk/Dresser – Broken / Damaged',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Desk/Dresser – Drawer Broken',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Desk/Dresser – Tape Residue',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microwave Oven Rack – Broken / Missing',50);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Mattress – Clean',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Mattress – Replace',.00);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microwave / Oven Tray – Burnt',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microwave – Replace',225);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microfridge – Glass plate',35);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microfridge – Roller under glass plate',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microfridge – Fridge top/bottom shelf',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Microfridge – Scraper (foot)',0);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Mirror – Replace',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Mirror – Tape Residue',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Shelves – Bracket Missing / Broken',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Shelves – Missing shelf',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Shelves – Scratches',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Shower Curtain – Missing / Torn',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Shower Curtain Hook – Missing (per hook)',2);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Towel Bar – Broken / Missing',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Towel Bar – Dented',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Remount Built-in Furniture',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Furnishings','Scratched / Stained / Carved',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Keys','Apartment / Suite',45);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Keys','Traditional Residence Hall',45);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Keys','Winkler Suite/App. Heights/LLC',45);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Trash','Cinder Block Removal',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Trash','Furniture Removal',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Trash','Trash Removal',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Blinds – Broken (Head Rail Repair)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Blinds – Fin Replacement (per fin)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Blinds – Replace (Vertical)',160);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Blinds – Replace (Mini)',40);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Replace Glass – Broken/Cracked for Small',75);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Replace Glass – Broken/Cracked for Large',150);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Glass – Tape Residue',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Screen – Clean',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Screen – Damaged/Bent/Replace',50);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Window','Screen – Reinstall',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Cracks (per wall)',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Holes – 1 – 3 (per wall)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Holes – 3 – 10 (per wall)',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Holes – 10 or more (per wall)',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Hole – other than pin / nail holes',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Outlet Cover – damaged / missing',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Paint (per wall)',40);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Scratches / tears (per wall)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Scuff marks (per wall)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Walls','Tape residue (per wall)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'DSL Equipment','Power Adapter for modem (Justice/Winkler)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'DSL Equipment','Corecess 3115 DSL Modem (Justice/Winkler)',60);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'DSL Equipment','Micro-filer (Justice/Winkler)',15);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'DSL Equipment','Phone cable (Justice/Winkler)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'DSL Equipment','Ethernet cable (Justice/Winkler)',10);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'DSL Equipment','Power adapter for Ethernet hub (Justice)',40);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Boxing Belongings',75);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Improper Check Out',35);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Cost of Boxes',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Housekeeping Labor (per hour)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Housekeeping Labor (per hour OT)',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Support Services Labor (per hour)',20);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Support Services Labor (per hour OT)',30);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Maintenance Labor (per hour)',25);
INSERT INTO hms_damage_type VALUES (nextval('hms_damage_type_seq'),'Miscellaneous','Maintenance Labor (per hour OT)',37.50);