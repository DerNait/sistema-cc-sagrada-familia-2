<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Calificaciones</title>
    <style>
        /* --------- Configuración general y de impresión --------- */
        @page { margin: 20mm; }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.35;
        }
        h1, h2, h3, h4, h5 { margin: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mt-1 { margin-top: .25rem; } .mt-2 { margin-top: .5rem; } .mt-3 { margin-top: .75rem; }
        .mb-1 { margin-bottom: .25rem; } .mb-2 { margin-bottom: .5rem; } .mb-3 { margin-bottom: .75rem; }
        .py-1 { padding-top: .25rem; padding-bottom: .25rem; }
        .py-2 { padding-top: .5rem;  padding-bottom: .5rem; }
        .px-2 { padding-left: .5rem; padding-right: .5rem; }
        .small { font-size: 11px; }
        .muted { color: #666; }
        .brand-green { color: #2e7d32; }
        .bg-brand { background: #e8f5e9; }
        .fw-bold { font-weight: 700; }
        .avoid-break { page-break-inside: avoid; }
        .hr { height: 1px; background: #ddd; margin: 10px 0; }

        /* Layout de encabezado */
        .header {
            display: grid;
            grid-template-columns: 120px 1fr 120px;
            gap: 12px;
            align-items: center;
        }
        .header .title {
            text-align: center;
        }
        .header .title h1 {
            font-size: 20px;
            letter-spacing: .3px;
        }
        .header .title .subtitle { font-size: 12px; }
        .logo {
            width: 100%;
            height: 70px;
            object-fit: contain;
        }

        /* Tablas base */
        table { width: 100%; border-collapse: collapse; }
        .table { border: 1px solid #bdbdbd; }
        .table th, .table td { border: 1px solid #bdbdbd; padding: 6px 8px; }
        .table thead th {
            background: #2e7d32;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .3px;
        }
        .table tfoot td {
            background: #f3f3f3;
            font-weight: 700;
        }
        .table.striped tbody tr:nth-child(odd) { background: #fafafa; }

        /* Tabla de ficha del estudiante */
        .card-table th {
            background: #e0e0e0;
            color: #111;
            width: 15%;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
        }
        .card-table td { background: #fff; }

        /* Asistencia en 4 columnas */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .mini-card {
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            overflow: hidden;
        }
        .mini-card .mini-header {
            background: #2e7d32;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 6px 8px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .4px;
        }
        .mini-card table { width: 100%; border-collapse: collapse; }
        .mini-card td { border-top: 1px solid #e0e0e0; padding: 6px 8px; }
        .mini-card td.label { color: #444; }
        .mini-card td.value { text-align: right; font-weight: 700; }

        /* Observaciones */
        .observaciones {
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            padding: 10px 12px;
            background: #fafafa;
            font-style: italic;
        }

        /* Asegurar buena salida a PDF */
        .print-block { margin-top: 10px; margin-bottom: 14px; }
        .w-100 { width: 100%; }
    </style>
</head>
<body>
    <!-- Encabezado institucional -->
    <section class="print-block avoid-break">
        <div class="header">
            <div>
                <!-- Si usas DomPDF, public_path funciona -->
                <img class="logo" src="{{ public_path('images/logo.png') }}" alt="Logo">
            </div>
            <div class="title">
                <h1 class="brand-green">Centro Cultural Sagrada Familia 2</h1>
                <div class="subtitle">
                    Lotes 64, 65 y 66, manzana A, sector 10 – Prados de Villa Hermosa, San Miguel Petapa<br>
                    Tel. 24646082 — Prados de Villa Hermosa San Miguel Petapa — Guatemala
                </div>
            </div>
            <div></div>
        </div>
    </section>

    <!-- Datos del estudiante -->
    <section class="print-block avoid-break">
        <table class="table card-table">
            <tbody>
                <tr>
                    <th>Nombre:</th>
                    <td>Yong Batz Jennifer Paola</td>
                    <th>Clave:</th>
                    <td>10</td>
                    <th>Ciclo escolar:</th>
                    <td>2025</td>
                </tr>
                <tr>
                    <th>Grado:</th>
                    <td>Sexto Magisterio de Educación Infantil Bilingüe Intercultural</td>
                    <th>Sección:</th>
                    <td>A</td>
                    <th>Jornada:</th>
                    <td>Matutina</td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- Tabla de calificaciones -->
    <section class="print-block avoid-break">
        <table class="table striped">
            <thead>
                <tr>
                    <th style="width: 40px;">No.</th>
                    <th>Curso</th>
                    <th style="width: 80px;">Bloque I</th>
                    <th style="width: 80px;">Bloque II</th>
                    <th style="width: 80px;">Bloque III</th>
                    <th style="width: 80px;">Bloque IV</th>
                    <th style="width: 90px;">Acumulado</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1</td><td>Realidad Social, Cultural: Cultura, Identidad y Derechos Humanos</td><td>76</td><td>82</td><td>69</td><td>74</td><td>75</td></tr>
                <tr><td>2</td><td>Medio Social y Natural II y su didáctica</td><td>67</td><td>64</td><td>80</td><td><span style="color:#c62828; font-weight:700;">47</span></td><td>65</td></tr>
                <tr><td>3</td><td>Química</td><td>98</td><td>84</td><td>89</td><td>100</td><td>93</td></tr>
                <tr><td>4</td><td>Didáctica del Idioma Materno (L1)</td><td>91</td><td>94</td><td>87</td><td>89</td><td>90</td></tr>
                <tr><td>5</td><td>Didáctica del Segundo Idioma (L2)</td><td>69</td><td>81</td><td>87</td><td>95</td><td>83</td></tr>
                <tr><td>6</td><td>Didáctica del Tercer Idioma (L3)</td><td>85</td><td>95</td><td>79</td><td>92</td><td>88</td></tr>
                <tr><td>7</td><td>Literatura Universal</td><td>90</td><td>85</td><td>78</td><td>80</td><td>83</td></tr>
                <tr><td>8</td><td>Literatura Maya, Ladina, Xinca y Garífuna</td><td><span style="color:#c62828; font-weight:700;">59</span></td><td>85</td><td>76</td><td>88</td><td>77</td></tr>
                <tr><td>9</td><td>Expresión Artística y Corporal III</td><td>67</td><td>99</td><td>85</td><td>88</td><td>85</td></tr>
                <tr><td>10</td><td>Enfoques Pedagógicos</td><td>62</td><td>69</td><td><span style="color:#c62828; font-weight:700;">46</span></td><td>67</td><td>61</td></tr>
                <tr><td>11</td><td>Práctica Docente</td><td>79</td><td>80</td><td>88</td><td>80</td><td>82</td></tr>
                <tr><td>12</td><td>Elaboración de Material Didáctico</td><td>65</td><td>95</td><td>94</td><td>82</td><td>84</td></tr>
                <tr><td>13</td><td>Lógica Matemática</td><td>66</td><td>90</td><td>92</td><td>89</td><td>84</td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">PROMEDIO GENERAL</td>
                    <td>81</td>
                </tr>
            </tfoot>
        </table>
    </section>

    <!-- Asistencia -->
    <section class="print-block avoid-break">
        <h3 class="brand-green mb-2">ASISTENCIA</h3>
        <div class="grid-4">
            <div class="mini-card">
                <div class="mini-header">Bloque I</div>
                <table>
                    <tr><td class="label">Días ausente</td><td class="value">3</td></tr>
                    <tr><td class="label">Días presente</td><td class="value">28</td></tr>
                    <tr><td class="label">Llegadas tarde</td><td class="value">1</td></tr>
                </table>
            </div>
            <div class="mini-card">
                <div class="mini-header">Bloque II</div>
                <table>
                    <tr><td class="label">Días ausente</td><td class="value">5</td></tr>
                    <tr><td class="label">Días presente</td><td class="value">32</td></tr>
                    <tr><td class="label">Llegadas tarde</td><td class="value">1</td></tr>
                </table>
            </div>
            <div class="mini-card">
                <div class="mini-header">Bloque III</div>
                <table>
                    <tr><td class="label">Días ausente</td><td class="value">8</td></tr>
                    <tr><td class="label">Días presente</td><td class="value">27</td></tr>
                    <tr><td class="label">Llegadas tarde</td><td class="value">2</td></tr>
                </table>
            </div>
            <div class="mini-card">
                <div class="mini-header">Bloque IV</div>
                <table>
                    <tr><td class="label">Días ausente</td><td class="value">8</td></tr>
                    <tr><td class="label">Días presente</td><td class="value">22</td></tr>
                    <tr><td class="label">Llegadas tarde</td><td class="value">0</td></tr>
                </table>
            </div>
        </div>
    </section>

    <!-- Observaciones -->
    <section class="print-block avoid-break">
        <h3 class="brand-green mb-2">OBSERVACIONES</h3>
        <div class="observaciones">
            "Este es tu momento. Has demostrado tu valía, ahora ve y haz la diferencia en el mundo."
        </div>
    </section>
</body>
</html>
