@extends('layouts.app')

@section('content')
<div class="px-10 pb-20 max-w-full mx-auto"
    x-data="{
        vehiculos: {{ $vehiculos->toJson() }},
        empresas: {{ $empresas->toJson() }},
        search: '',
        empresaFiltro: '',
        selectedVehiculo: null,
        selectedTab: 'vinculo',
        activeView: 'vehiculos', // 'vehiculos', 'perfil'
        detailLoading: false,
        detailData: null,
        currentPage: 1,
        perPage: 15,
        
        peVehCurrentPage: 1,
        peVehPerPage: 15,
        peCondCurrentPage: 1,
        peCondPerPage: 15,
        pePropCurrentPage: 1,
        pePropPerPage: 15,
        peRepCurrentPage: 1,
        peRepPerPage: 15,
        
        init() {
            this.$watch('search', () => { this.currentPage = 1; });
            this.$watch('empresaFiltro', () => { this.currentPage = 1; });
            this.$watch('peSearchTerm', () => { this.peVehCurrentPage = 1; });
            this.$watch('peFilterEstado', () => { this.peVehCurrentPage = 1; });
            this.$watch('fechaInicio', () => { this.peRepCurrentPage = 1; });
            this.$watch('fechaFin', () => { this.peRepCurrentPage = 1; });
            this.$watch('peSubTab', () => {
                this.peVehCurrentPage = 1;
                this.peCondCurrentPage = 1;
                this.pePropCurrentPage = 1;
                this.peRepCurrentPage = 1;
            });
            this.$watch('activeView', () => {
                this.peVehCurrentPage = 1;
                this.peCondCurrentPage = 1;
                this.pePropCurrentPage = 1;
                this.peRepCurrentPage = 1;
            });
        },

        /* ──── Flujo Perfil Empresa ──── */
        peSubTab: 'vehiculos',
        
        get peConductores() {
            if (!this.selectedVehiculo || !this.selectedVehiculo.empresa) return [];
            let vehs = this.vehiculos.filter(v => v.idemp === this.selectedVehiculo.empresa.idemp && v.conductor);
            let map = new Map();
            vehs.forEach(v => {
                if (!map.has(v.conductor.idper)) map.set(v.conductor.idper, v.conductor);
            });
            return Array.from(map.values());
        },

        get pePropietarios() {
            if (!this.selectedVehiculo || !this.selectedVehiculo.empresa) return [];
            let vehs = this.vehiculos.filter(v => v.idemp === this.selectedVehiculo.empresa.idemp && v.propietario);
            let map = new Map();
            vehs.forEach(v => {
                if (!map.has(v.propietario.idper)) map.set(v.propietario.idper, v.propietario);
            });
            return Array.from(map.values());
        },

        /* ──── Filtros Perfil Empresa ──── */
        peSearchTerm: '',
        peFilterEstado: '',
        get peFilteredVehiculos() {
            if (!this.selectedVehiculo || !this.selectedVehiculo.empresa) return [];
            let list = this.vehiculos.filter(v => v.idemp === this.selectedVehiculo.empresa.idemp);
            
            if (this.peSearchTerm) {
                const term = this.peSearchTerm.toLowerCase();
                list = list.filter(v => 
                    (v.placaveh && v.placaveh.toLowerCase().includes(term)) ||
                    (this.vehiculoModelo(v).toLowerCase().includes(term))
                );
            }

            if (this.peFilterEstado && this.peFilterEstado !== 'Todos') {
                list = list.filter(v => {
                    const st = this.getVehiculoEstadoPerfil(v.idveh).texto;
                    return st === this.peFilterEstado;
                });
            }

            return list;
        },
        getVehiculoEstadoPerfil(idveh) {
            if (!this.detailData || !this.detailData.reporte_flota) return { texto: 'Pendiente', clase: 'inactive' };
            const diags = this.detailData.reporte_flota.filter(d => d.idveh === idveh);
            if (diags.length === 0) return { texto: 'Pendiente', clase: 'inactive' };
            const lastDiag = diags[0];
            if (lastDiag.aprobado === 1) return { texto: 'Aprobado', clase: 'success' };
            if (lastDiag.aprobado === 0) return { texto: 'No Aprobado', clase: 'danger' };
            return { texto: 'Pendiente', clase: 'warning' };
        },
        
        /* ──── Filtros Reporte ──── */
        fechaInicio: '',
        fechaFin: '',
        reporteQuickMes: '',
        reporteQuickAnio: '',

        setQuickMes(mes) {
            this.reporteQuickMes = mes;
            if (!mes) { this.fechaInicio = ''; this.fechaFin = ''; return; }
            const anio = this.reporteQuickAnio || new Date().getFullYear();
            const m = String(mes).padStart(2, '0');
            const lastDay = new Date(anio, mes, 0).getDate();
            this.fechaInicio = `${anio}-${m}-01`;
            this.fechaFin = `${anio}-${m}-${String(lastDay).padStart(2,'0')}`;
        },
        setQuickAnio(anio) {
            this.reporteQuickAnio = anio;
            if (!anio) { this.fechaInicio = ''; this.fechaFin = ''; this.reporteQuickMes = ''; return; }
            if (this.reporteQuickMes) {
                this.setQuickMes(this.reporteQuickMes);
            } else {
                this.fechaInicio = `${anio}-01-01`;
                this.fechaFin = `${anio}-12-31`;
            }
        },
        clearReporteFilters() {
            this.fechaInicio = '';
            this.fechaFin = '';
            this.reporteQuickMes = '';
            this.reporteQuickAnio = '';
        },

        get reportesFiltrados() {
            if (!this.detailData || !this.detailData.reporte_flota) return [];
            let r = this.detailData.reporte_flota;
            if (this.fechaInicio) {
                const fIni = new Date(this.fechaInicio + 'T00:00:00');
                r = r.filter(x => new Date(x.fecdia) >= fIni);
            }
            if (this.fechaFin) {
                const fFin = new Date(this.fechaFin + 'T23:59:59');
                r = r.filter(x => new Date(x.fecdia) <= fFin);
            }
            return r;
        },
        get reporteResumen() {
            const r = this.reportesFiltrados;
            return {
                total: r.length,
                aprobados: r.filter(x => x.aprobado === 1).length,
                noAprobados: r.filter(x => x.aprobado === 0).length,
            };
        },
        get reporteAniosDisponibles() {
            if (!this.detailData || !this.detailData.reporte_flota) return [];
            const years = new Set(this.detailData.reporte_flota.map(d => new Date(d.fecdia).getFullYear()));
            return Array.from(years).sort((a,b) => b - a);
        },
        paramResumen(diag) {
            if (!diag.parametros || diag.parametros.length === 0) return 'Sin parámetros';
            const total = diag.parametros.length;
            let fallos = 0;
            diag.parametros.forEach(p => {
                const meta = p.parametro;
                if (!meta) return;
                if (meta.control === 'radio' && (p.valor === 'no' || p.valor === 'no_funciona')) fallos++;
                if (meta.control === 'number' && meta.rini !== null && meta.rfin !== null) {
                    if (parseFloat(p.valor) < parseFloat(meta.rini) || parseFloat(p.valor) > parseFloat(meta.rfin)) fallos++;
                }
            });
            if (fallos === 0) return total + ' parámetros — Todo OK';
            return total + ' parámetros — ' + fallos + ' observación(es)';
        },
        canExportDiag(rep) {
            if (rep.aprobado === null) return false;
            if (rep.rechazo && rep.rechazo.estadorec === 'Reasignado') return false;
            return true;
        },
        exportUrl(diagId) {
            const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
            return '/' + prefix + '/diagnosticos/' + diagId + '/export';
        },
        exportarFlota() {
            const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
            const empresaId = this.selectedVehiculo?.empresa?.idemp;
            if (!empresaId) return;
            let url = '/' + prefix + '/vehiculos-empresa/export-flota?empresa_id=' + empresaId;
            if (this.fechaInicio) url += '&fecha_inicio=' + this.fechaInicio;
            if (this.fechaFin) url += '&fecha_fin=' + this.fechaFin;
            window.open(url, '_blank');
        },
        get reportesExportables() {
            return this.reportesFiltrados.filter(r => this.canExportDiag(r));
        },

        /* ──── Edición de empresa (Admin/Digitador) ──── */
        editandoEmpresa: false,
        editForm: { idemp: '' },
        editSaving: false,

        /* ──── Edición de PERFIL de Empresa ──── */
        editandoPerfilEmpresa: false,
        perfilForm: { idemp: '', razsoem: '', nonitem: '', direm: '', telem: '', nomger: '' },
        perfilSaving: false,
        
        openEditPerfil() {
            if(!this.selectedVehiculo || !this.selectedVehiculo.empresa) return;
            const emp = this.selectedVehiculo.empresa;
            this.perfilForm = {
                idemp: emp.idemp,
                razsoem: emp.razsoem || '',
                nonitem: emp.nonitem || '',
                direm: emp.direm || '',
                telem: emp.telem || '',
                nomger: emp.nomger || ''
            };
            this.editandoPerfilEmpresa = true;
        },
        cancelEditPerfil() { this.editandoPerfilEmpresa = false; },
        async saveEditPerfil() {
            this.perfilSaving = true;
            try {
                const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
                const res = await fetch('/' + prefix + '/vehiculos-empresa/perfil/' + this.perfilForm.idemp, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.perfilForm)
                });
                const data = await res.json();
                if (data.success) {
                    const refEmp = this.empresas.find(e => e.idemp === this.perfilForm.idemp);
                    if (refEmp) Object.assign(refEmp, data.empresa);
                    
                    if(this.selectedVehiculo && this.selectedVehiculo.idemp === data.empresa.idemp) {
                        this.selectedVehiculo.empresa = data.empresa;
                    }
                    this.vehiculos.forEach(v => {
                        if(v.idemp === data.empresa.idemp) v.empresa = data.empresa;
                    });
                    this.editandoPerfilEmpresa = false;
                } else {
                    alert('Error: ' + (data.message || 'Verifique sus datos'));
                }
            } catch(e) { console.error('Error:', e); alert('Error de red'); }
            finally { this.perfilSaving = false; }
        },

        /* ──── Helpers ──── */
        vehiculoModelo(v) {
            return (v.marca ? v.marca.nommarlin : 'N/A') + (v.modveh ? ' · ' + v.modveh : '');
        },
        diagResultado(d) {
            if (!d) return 'Sin diagnósticos';
            if (d.aprobado === 1) return 'Aprobado';
            if (d.aprobado === 0) return 'No aprobado';
            return 'Pendiente';
        },
        diagResultadoClass(d) {
            if (!d) return 'inactive';
            if (d.aprobado === 1) return 'success';
            if (d.aprobado === 0) return 'danger';
            return 'warning';
        },
        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const d = new Date(dateStr);
            return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
        },
        formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },

        /* Conteo instantáneo de vehículos vinculados a una empresa (desde array cliente) */
        vehiculosEnEmpresa(idemp) {
            if (!idemp) return 0;
            return this.vehiculos.filter(v => v.idemp === idemp).length;
        },

        /* ──── Filtros ──── */
        get filteredVehiculos() {
            return this.vehiculos.filter(v => {
                const term = this.search.toLowerCase();
                const matchSearch = !term ||
                    (v.placaveh && v.placaveh.toLowerCase().includes(term)) ||
                    (v.nordveh && v.nordveh.toLowerCase().includes(term)) ||
                    (v.empresa && v.empresa.nonitem && v.empresa.nonitem.toLowerCase().includes(term)) ||
                    (v.empresa && v.empresa.razsoem && v.empresa.razsoem.toLowerCase().includes(term));
                const matchEmpresa = this.empresaFiltro === '' ||
                    (v.empresa && String(v.empresa.idemp) === this.empresaFiltro);
                return matchSearch && matchEmpresa;
            });
        },
        get totalItems() {
            return this.filteredVehiculos.length;
        },
        get totalPages() {
            return Math.ceil(this.totalItems / this.perPage) || 1;
        },
        get paginationArray() {
            return this.buildPaginationArray(this.totalItems, this.perPage, this.currentPage);
        },
        buildPaginationArray(totalItems, perPage, currentPage) {
            let totalPages = Math.ceil(totalItems / perPage) || 1;
            let current = currentPage;
            let last = totalPages;
            let delta = 2;
            let left = current - delta;
            let right = current + delta + 1;
            let range = [];
            let rangeWithDots = [];
            let l;

            for (let i = 1; i <= last; i++) {
                if (i === 1 || i === last || (i >= left && i < right)) {
                    range.push(i);
                }
            }

            for (let i of range) {
                if (l) {
                    if (i - l === 2) {
                        rangeWithDots.push(l + 1);
                    } else if (i - l !== 1) {
                        rangeWithDots.push('...');
                    }
                }
                rangeWithDots.push(i);
                l = i;
            }

            return rangeWithDots;
        },
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        get displayedVehiculos() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredVehiculos.slice(start, end);
        },

        /* ──── Selección y detalle ──── */
        async selectVehiculo(v) {
            this.selectedVehiculo = v;
            this.selectedTab = 'vinculo';
            this.editandoEmpresa = false;
            this.detailLoading = true;
            setTimeout(() => {
                const el = document.getElementById('vehiculo-detalles');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
            try {
                const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
                const res = await fetch('/' + prefix + '/vehiculos-empresa/' + v.idveh, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                this.detailData = await res.json();
            } catch(e) {
                console.error('Error cargando detalle:', e);
                this.detailData = null;
            } finally {
                this.detailLoading = false;
            }
        },

        /* ──── Edición de vínculo empresa ──── */
        openEditEmpresa() {
            this.editForm.idemp = this.selectedVehiculo.idemp || '';
            this.editandoEmpresa = true;
        },
        cancelEditEmpresa() { this.editandoEmpresa = false; },
        async saveEditEmpresa() {
            this.editSaving = true;
            try {
                const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
                const res = await fetch('/' + prefix + '/vehiculos-empresa/' + this.selectedVehiculo.idveh + '/vinculo', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ idemp: this.editForm.idemp || null })
                });
                const data = await res.json();
                if (data.success) {
                    const idx = this.vehiculos.findIndex(vv => vv.idveh === this.selectedVehiculo.idveh);
                    if (idx !== -1) this.vehiculos[idx] = data.vehiculo;
                    this.selectedVehiculo = data.vehiculo;
                    this.editandoEmpresa = false;
                    this.selectVehiculo(data.vehiculo);
                }
            } catch(e) { console.error('Error:', e); }
            finally { this.editSaving = false; }
        },

        /* ──── Init ──── */
        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('view') === 'perfil') {
                this.activeView = 'perfil';
            }
            if (this.vehiculos.length > 0) {
                this.selectVehiculo(this.vehiculos[0]);
            }
        }
    }">

    <style>
        /* ═══════════════  MÓDULO VEHÍCULOS x EMPRESA  ═══════════════ */
        .vemp-module { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #0b2540; }

        /* Botones */
        .vemp-module .vbtn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; border: 1px solid transparent; white-space: nowrap; }
        .vemp-module .vbtn-primary { background-color: #0b3a5a; color: #ffffff; }
        .vemp-module .vbtn-primary:hover { background-color: #082d46; }
        .vemp-module .vbtn-secondary { background-color: #ffffff; border-color: #d1d5db; color: #374151; }
        .vemp-module .vbtn-secondary:hover { background-color: #f9fafb; }
        .vemp-module .vbtn-outline { background-color: transparent; border-color: #d1d5db; color: #374151; }
        .vemp-module .vbtn-outline:hover { background-color: #f3f4f6; }
        .vemp-module .vbtn-outline-dashed { background-color: transparent; border-color: #d1d5db; border-style: dashed; color: #374151; }
        .vemp-module .vbtn-outline-dashed:hover { background-color: #f9fafb; border-color: #9ca3af; }
        .vemp-module .vbtn-edit { background-color: #f59e0b; color: #ffffff; }
        .vemp-module .vbtn-edit:hover { background-color: #d97706; }

        /* Tarjetas */
        .vemp-module .vcard { background: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.025); border: 1px solid rgba(0,0,0,0.08); overflow: hidden; }

        /* Header */
        .vemp-module .page-title { font-size: 24px; font-weight: 700; margin: 0 0 6px 0; }
        .vemp-module .page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }

        /* Tabla */
        .vemp-module .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .vemp-module .data-table th { padding: 14px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f9fafb; border-bottom: 1px solid rgba(0,0,0,0.08); white-space: nowrap; }
        .vemp-module .data-table td { padding: 16px 24px; font-size: 14px; color: #374151; border-bottom: 1px solid rgba(0,0,0,0.08); vertical-align: middle; white-space: nowrap; }
        .vemp-module .data-table tbody tr { cursor: pointer; transition: background-color 0.2s; }
        .vemp-module .data-table tbody tr:hover { background-color: #f9fafb; }
        .vemp-module .data-table tr.active-row { background-color: #eff6ff; }

        /* Badges */
        .vemp-module .plate-badge { background-color: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-family: monospace; font-size: 13px; letter-spacing: 0.5px; display: inline-block; }
        .vemp-module .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .vemp-module .status-badge.success { background-color: #dcfce7; color: #15803d; }
        .vemp-module .status-badge.info { background-color: #dbeafe; color: #1d4ed8; }
        .vemp-module .status-badge.inactive { background-color: #f3f4f6; color: #4b5563; }
        .vemp-module .status-badge.danger { background-color: #fef2f2; color: #dc2626; }
        .vemp-module .status-badge.warning { background-color: #fef3c7; color: #92400e; }

        /* Company + vehicle cells */
        .vemp-module .company-cell { display: flex; flex-direction: column; gap: 4px; }
        .vemp-module .company-cell .company-name { font-weight: 600; color: #111827; }
        .vemp-module .company-cell .company-nit { font-size: 12px; color: #6b7280; }
        .vemp-module .vehicle-cell { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
        .vemp-module .vehicle-cell .vehicle-model { font-size: 12px; color: #6b7280; }

        /* Icon buttons */
        .vemp-module .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; color: #6b7280; cursor: pointer; transition: 0.2s; }
        .vemp-module .icon-btn:hover { background: #e5e7eb; color: #111827; }

        /* Detail section */
        .vemp-module .details-section { display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; align-items: start; }

        /* Tabs */
        .vemp-module .vtabs { display: flex; gap: 24px; border-bottom: 1px solid rgba(0,0,0,0.08); padding: 0 24px; }
        .vemp-module .vtab { padding: 12px 0; background: none; border: none; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: 0.2s; }
        .vemp-module .vtab:hover { color: #111827; }
        .vemp-module .vtab.active { color: #0b3a5a; border-bottom-color: #0b3a5a; font-weight: 600; }

        /* Form / Detail */
        .vemp-module .form-scroll-area { max-height: 520px; overflow-y: auto; background-color: #f9fafb; }
        .vemp-module .form-scroll-area::-webkit-scrollbar { width: 6px; }
        .vemp-module .form-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .vemp-module .form-scroll-area::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
        .vemp-module .form-section { padding: 24px; background: #ffffff; border-bottom: 1px solid rgba(0,0,0,0.08); }
        .vemp-module .form-section:last-child { border-bottom: none; }
        .vemp-module .form-section-title { margin: 0 0 20px 0; font-size: 15px; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 8px; }
        .vemp-module .form-section-title i { color: #0b3a5a; font-size: 16px; }
        .vemp-module .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 20px; }
        .vemp-module .form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .vemp-module .form-group { display: flex; flex-direction: column; gap: 6px; }
        .vemp-module .form-group.col-span-2 { grid-column: span 2; }
        .vemp-module .form-group label { font-size: 13px; font-weight: 500; color: #4b5563; }
        .vemp-module .field-shell { min-height: 40px; display: flex; align-items: center; padding: 0 12px; border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; background: #ffffff; font-size: 14px; color: #111827; }
        .vemp-module .field-shell.na { color: #9ca3af; font-style: italic; }
        .vemp-module .field-shell-area { min-height: 80px; align-items: flex-start; padding-top: 12px; white-space: normal; line-height: 1.5; }

        /* Card footer */
        .vemp-module .card-footer { padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.08); display: flex; justify-content: flex-end; gap: 12px; background: #ffffff; }

        /* ═══ Company Summary Card (reference-based) ═══ */
        .vemp-module .company-summary-card { height: 100%; display: flex; flex-direction: column; }
        .vemp-module .cscard-header { padding: 28px 24px; border-bottom: 1px solid rgba(0,0,0,0.08); display: flex; align-items: center; gap: 20px; }
        .vemp-module .cscard-logo { width: 64px; height: 64px; border-radius: 12px; background: rgba(11,58,90,0.08); color: #0b3a5a; display: flex; align-items: center; justify-content: center; font-size: 30px; flex-shrink: 0; }
        .vemp-module .cscard-title-area { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 0; }
        .vemp-module .cscard-name { font-size: 20px; font-weight: 600; color: #0f1724; margin: 0; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .vemp-module .cscard-nit-status { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .vemp-module .cscard-nit { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 5px; }
        .vemp-module .cscard-nit i { font-size: 12px; }
        .vemp-module .cscard-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; background: rgba(30,164,90,0.12); color: #15803d; }
        .vemp-module .cscard-body { padding: 24px; background: color-mix(in srgb, #f0f2f4 30%, #f6f7f9); flex: 1; display: flex; flex-direction: column; gap: 20px; }
        .vemp-module .cscard-stats { background: #ffffff; border: 1px solid rgba(0,0,0,0.08); border-radius: 10px; padding: 20px 22px; display: flex; flex-direction: column; gap: 18px; }
        .vemp-module .stat-row { display: flex; justify-content: space-between; align-items: center; }
        .vemp-module .stat-label { display: flex; align-items: center; gap: 10px; color: #4b5563; font-size: 14px; font-weight: 500; }
        .vemp-module .stat-label i { color: #9ca3af; font-size: 15px; }
        .vemp-module .stat-value { font-size: 16px; font-weight: 600; color: #0f1724; }
        .vemp-module .stat-value.normal { font-weight: 500; font-size: 14px; }
        .vemp-module .stat-value.accent { color: #0ba5d8; }
        .vemp-module .cscard-footer { padding: 18px 24px; border-top: 1px solid rgba(0,0,0,0.08); display: flex; gap: 12px; background: #ffffff; }

        /* Show more */
        .vemp-module .show-more-bar { padding: 12px 24px; text-align: center; border-top: 1px solid rgba(0,0,0,0.05); background: #f9fafb; }
        .vemp-module .show-more-bar button { background: none; border: none; color: #0b3a5a; font-weight: 600; font-size: 13px; cursor: pointer; padding: 6px 16px; border-radius: 6px; transition: 0.2s; }
        .vemp-module .show-more-bar button:hover { background: #eff6ff; }

        /* ═══ Timeline (reference-based) ═══ */
        .vemp-module .vtimeline { display: flex; flex-direction: column; gap: 24px; position: relative; padding-left: 28px; margin-top: 8px; }
        .vemp-module .vtimeline::before { content: ''; position: absolute; left: 7px; top: 6px; bottom: 0; width: 2px; background-color: #e5e7eb; }
        .vemp-module .vtimeline-item { position: relative; display: flex; flex-direction: column; gap: 6px; }
        .vemp-module .vtimeline-icon { position: absolute; left: -28px; top: 2px; width: 16px; height: 16px; border-radius: 50%; background-color: #ffffff; border: 3px solid #0b3a5a; box-shadow: 0 0 0 4px #ffffff; z-index: 1; }
        .vemp-module .vtimeline-icon.tl-success { border-color: #1ea45a; }
        .vemp-module .vtimeline-icon.tl-warning { border-color: #f59e0b; }
        .vemp-module .vtimeline-icon.tl-muted { border-color: #9ca3af; }
        .vemp-module .vtimeline-icon.tl-system { border-color: #0ba5d8; }
        .vemp-module .vtimeline-date { font-size: 13px; color: #6b7280; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .vemp-module .vtimeline-date i { font-size: 13px; }
        .vemp-module .vtimeline-card { background: #ffffff; border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 16px; margin-top: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .vemp-module .vtimeline-title { font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 6px 0; }
        .vemp-module .vtimeline-desc { font-size: 13px; color: #4b5563; margin: 0; line-height: 1.5; }
        .vemp-module .vtimeline-user { display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; font-size: 12px; color: #6b7280; background-color: #f9fafb; padding: 6px 10px; border-radius: 6px; }
        .vemp-module .vtimeline-user i { color: #9ca3af; }

        /* ═══ Upload Zone (reference-based) ═══ */
        .vemp-module .upload-zone { min-height: 180px; border: 1px dashed #d1d5db; border-radius: 12px; background: color-mix(in srgb, #f6f7f9 75%, #ffffff); display: flex; align-items: center; justify-content: center; text-align: center; padding: 32px; transition: border-color 0.2s, background 0.2s; cursor: pointer; }
        .vemp-module .upload-zone:hover { border-color: #9ca3af; background: #f0f2f4; }
        .vemp-module .upload-zone .upload-content { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .vemp-module .upload-zone .upload-icon { font-size: 38px; color: #9ca3af; }
        .vemp-module .upload-zone .upload-title { font-size: 16px; font-weight: 600; color: #0f1724; }
        .vemp-module .upload-zone .upload-subtitle { font-size: 14px; color: #9ca3af; line-height: 1.5; }

        /* ═══ Documents List (reference-based) ═══ */
        .vemp-module .doc-list { display: flex; flex-direction: column; gap: 12px; }
        .vemp-module .doc-item { display: flex; align-items: center; gap: 16px; padding: 16px; border: 1px solid rgba(0,0,0,0.08); border-radius: 12px; background: #ffffff; transition: background 0.2s; }
        .vemp-module .doc-item:hover { background: #f9fafb; }
        .vemp-module .doc-icon { width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .vemp-module .doc-icon.pdf { background: rgba(220,38,38,0.08); color: #dc2626; }
        .vemp-module .doc-icon.img { background: rgba(11,165,216,0.1); color: #0ba5d8; }
        .vemp-module .doc-icon.other { background: #f3f4f6; color: #6b7280; }
        .vemp-module .doc-info { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .vemp-module .doc-name { font-size: 14px; font-weight: 600; color: #0f1724; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .vemp-module .doc-meta { font-size: 13px; color: #9ca3af; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .vemp-module .doc-actions { display: flex; align-items: center; gap: 6px; }

        /* Loading */
        .vemp-module .loading-pulse { animation: vempPulse 1.5s ease-in-out infinite; }
        @keyframes vempPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        /* Diag card inline */
        .vemp-module .diag-summary { display: flex; gap: 16px; padding: 16px; background: #f9fafb; border: 1px solid rgba(0,0,0,0.06); border-radius: 8px; }
        .vemp-module .diag-icon-box { width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .vemp-module .diag-icon-box.approved { background: #dcfce7; color: #15803d; }
        .vemp-module .diag-icon-box.rejected { background: #fef2f2; color: #dc2626; }
        .vemp-module .diag-icon-box.pending { background: #fef3c7; color: #92400e; }
        .vemp-module .diag-icon-box.none { background: #f3f4f6; color: #9ca3af; }

        @media (max-width: 1024px) {
            .vemp-module .details-section { grid-template-columns: 1fr; }
            .vemp-module .form-grid.cols-3 { grid-template-columns: repeat(2, 1fr); }
            .vemp-module .vtabs { overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; }
            .vemp-module .vtabs::-webkit-scrollbar { height: 4px; }
            .vemp-module .vtabs::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        }
        @media (max-width: 640px) {
            .vemp-module .form-grid { grid-template-columns: 1fr !important; }
            .vemp-module .form-group.col-span-2 { grid-column: span 1 !important; }
            .vemp-module .page-header-container { flex-direction: column; gap: 16px; }
            .vemp-module .filters-container { flex-direction: column; align-items: stretch; }
            .vemp-module .filters-container > div { width: 100%; max-width: 100% !important; }
            .vemp-module .filters-container select, .vemp-module .filters-container button { width: 100%; flex: 1; min-width: 120px; }
            .vemp-module .details-header { flex-direction: column; align-items: flex-start !important; gap: 12px; }
            .vemp-module .details-header .vbtn { width: 100%; justify-content: center; }
            .vemp-module .diag-summary { flex-direction: column; align-items: center; text-align: center; }
        }
    </style>

    <div class="vemp-module">
        {{-- Navegación superior eliminada por estética --}}

        {{-- ═══════════════════════════════════════ --}}
        {{--  VISTA 1: MAESTRO-DETALLE (Vehículos)   --}}
        {{-- ═══════════════════════════════════════ --}}
        <div x-show="activeView === 'vehiculos'">
            {{-- PAGE HEADER --}}
            <div class="page-header-container" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
                <div>
                    <h1 class="page-title">Flotas por Empresa</h1>
                    <p class="page-subtitle">Flota vinculada a entidades corporativas — solo vehículos con empresa asignada</p>
                </div>
            </div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- FILTERS BAR                             --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="filters-container" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; max-width: 400px; min-width: 200px;">
                <i class="fa-solid fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 14px;"></i>
                <input x-model="search" type="text" placeholder="Buscar por placa, N° interno o NIT..." style="width: 100%; height: 40px; padding-left: 38px; padding-right: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; font-family: inherit;" />
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                <select x-model="empresaFiltro" style="height: 40px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 32px 0 12px; font-size: 14px; background-color: #ffffff; outline: none; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%236B7280%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m6 9 6 6 6-6%27/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center;">
                    <option value="">Todas las empresas</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->idemp }}">{{ $emp->razsoem }}</option>
                    @endforeach
                </select>
                @endif
                <button class="vbtn vbtn-outline" @click="search=''; empresaFiltro=''">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- DATA TABLE                              --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="vcard" style="overflow-x: auto; margin-bottom: 24px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Placa</th>
                        <th>Línea / Modelo</th>
                        <th>Clase</th>
                        <th>Propietario</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="v in displayedVehiculos" :key="v.idveh">
                        <tr @click="selectVehiculo(v)" :class="{'active-row': selectedVehiculo && selectedVehiculo.idveh === v.idveh}">
                            <td>
                                <div class="company-cell">
                                    <span class="company-name" x-text="v.empresa?.razsoem || 'N/A'"></span>
                                    <span class="company-nit" x-text="'NIT: ' + (v.empresa?.nonitem || 'N/A')"></span>
                                </div>
                            </td>
                            <td>
                                <div class="vehicle-cell">
                                    <div class="plate-badge" x-text="v.placaveh"></div>
                                    <span class="vehicle-model" x-text="v.nordveh || ''"></span>
                                </div>
                            </td>
                            <td x-text="vehiculoModelo(v)"></td>
                            <td x-text="v.clase?.nomval || 'N/A'"></td>
                            <td x-text="v.propietario ? (v.propietario.nomper + ' ' + (v.propietario.apeper || '')) : 'N/A'"></td>
                        </tr>
                    </template>
                    <tr x-show="filteredVehiculos.length === 0">
                        <td colspan="5" style="text-align: center; padding: 32px; color: #6b7280;">
                            <i class="fa-solid fa-building-circle-xmark" style="font-size: 24px; margin-bottom: 8px; display: block; opacity: 0.3;"></i>
                            Ningún vehículo con empresa encontrado.
                        </td>
                    </tr>
                </tbody>
            </table>
            {{-- Pagination --}}
            <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-white border-t border-slate-200 gap-4">
                <div style="color: #64748b; font-size: 14px;">
                    Mostrando <span style="font-weight: 500; color: #0f172a;" x-text="(currentPage - 1) * perPage + (totalItems > 0 ? 1 : 0)"></span> a <span style="font-weight: 500; color: #0f172a;" x-text="Math.min(currentPage * perPage, totalItems)"></span> de <span style="font-weight: 500; color: #0f172a;" x-text="totalItems"></span> resultados
                </div>
                <div class="inline-flex border border-slate-200 rounded-md overflow-x-auto shadow-sm max-w-full" x-show="totalPages > 1" x-cloak>
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" 
                            :style="currentPage === 1 ? 'padding: 8px 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; border-right: 1px solid #e2e8f0; color: #64748b; cursor: pointer;'">
                        <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                    </button>
                    <template x-for="(page, index) in paginationArray" :key="index">
                        <button @click="page !== '...' ? goToPage(page) : null" 
                                :disabled="page === '...'"
                                x-text="page" 
                                :style="page === currentPage ? 'padding: 8px 14px; background: #f1f5f9; border-right: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; cursor: default;' : (page === '...' ? 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #94a3b8; cursor: default;' : 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #64748b; cursor: pointer;')">
                        </button>
                    </template>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" 
                            :style="currentPage === totalPages ? 'padding: 8px 12px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; color: #64748b; cursor: pointer;'">
                        <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- DETAIL SECTION                          --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="details-section" x-show="selectedVehiculo" x-cloak id="vehiculo-detalles">

            {{-- ── LEFT: Detail Card ── --}}
            <div class="vcard" style="display: flex; flex-direction: column; min-width: 0;">
                <div style="padding-top: 24px;">
                    <div class="details-header" style="display: flex; justify-content: space-between; align-items: center; padding: 0 24px; margin-bottom: 16px;">
                        <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0; white-space: nowrap;">Detalle del Vínculo</h3>
                        <div style="display: flex; gap: 8px; width: 100%; justify-content: flex-end;">
                            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                            <button class="vbtn vbtn-edit" @click="openEditEmpresa()" x-show="!editandoEmpresa">
                                <i class="fa-solid fa-pen"></i> Editar vínculo
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="vtabs">
                        <button class="vtab" :class="{'active': selectedTab === 'vinculo'}" @click="selectedTab = 'vinculo'">Vínculo y Vehículo</button>
                        <button class="vtab" :class="{'active': selectedTab === 'historial'}" @click="selectedTab = 'historial'">Historial</button>
                    </div>
                </div>

                {{-- Loading --}}
                <div x-show="detailLoading" style="padding: 48px; text-align: center;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: #0b3a5a;"></i>
                    <p class="loading-pulse" style="font-size: 13px; color: #6b7280; margin-top: 12px;">Cargando detalle...</p>
                </div>

                <div class="form-scroll-area" style="flex: 1;" x-show="!detailLoading">

                    {{-- ══ TAB: Vínculo y Vehículo ══ --}}
                    <template x-if="selectedTab === 'vinculo'">
                        <div>
                            {{-- Empresa vinculada --}}
                            <div class="form-section">
                                <h4 class="form-section-title"><i class="fa-solid fa-building"></i> Empresa Vinculada</h4>
                                <div class="form-grid cols-3">
                                    <div class="form-group col-span-2">
                                        <label>Razón Social</label>
                                        <div class="field-shell" x-text="selectedVehiculo.empresa?.razsoem || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>NIT</label>
                                        <div class="field-shell" x-text="selectedVehiculo.empresa?.nonitem || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Dirección</label>
                                        <div class="field-shell" x-text="selectedVehiculo.empresa?.direm || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <div class="field-shell" x-text="selectedVehiculo.empresa?.telem || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Gerente</label>
                                        <div class="field-shell" x-text="selectedVehiculo.empresa?.nomger || 'N/A'"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Datos del vehículo --}}
                            <div class="form-section">
                                <h4 class="form-section-title"><i class="fa-solid fa-car-side"></i> Datos del Vehículo</h4>
                                <div class="form-grid cols-3">
                                    <div class="form-group">
                                        <label>Placa</label>
                                        <div class="field-shell" style="font-family: monospace; font-weight: 600; letter-spacing: 1px;" x-text="selectedVehiculo.placaveh"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>No. Interno</label>
                                        <div class="field-shell" x-text="selectedVehiculo.nordveh || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Línea (Marca)</label>
                                        <div class="field-shell" x-text="selectedVehiculo.marca?.nommarlin || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Modelo (Año)</label>
                                        <div class="field-shell" x-text="selectedVehiculo.modveh || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Clase</label>
                                        <div class="field-shell" x-text="selectedVehiculo.clase?.nomval || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Propietario</label>
                                        <div class="field-shell" x-text="selectedVehiculo.propietario ? (selectedVehiculo.propietario.nomper + ' ' + (selectedVehiculo.propietario.apeper || '')) : 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Conductor</label>
                                        <div class="field-shell" x-text="selectedVehiculo.conductor ? (selectedVehiculo.conductor.nomper + ' ' + (selectedVehiculo.conductor.apeper || '')) : 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo de Servicio</label>
                                        <div class="field-shell" x-text="selectedVehiculo.tipo_servicio === 1 ? 'Particular' : 'Público'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Color</label>
                                        <div class="field-shell" x-text="selectedVehiculo.colveh || 'N/A'"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Cilindraje</label>
                                        <div class="field-shell" x-text="selectedVehiculo.cilveh ? selectedVehiculo.cilveh + ' cc' : 'N/A'"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Último Diagnóstico --}}
                            <div class="form-section">
                                <h4 class="form-section-title"><i class="fa-solid fa-stethoscope"></i> Último Diagnóstico</h4>
                                <template x-if="detailData?.ultimo_diag">
                                    <div>
                                        <div class="diag-summary">
                                            <div class="diag-icon-box" :class="detailData.ultimo_diag.aprobado === 1 ? 'approved' : (detailData.ultimo_diag.aprobado === 0 ? 'rejected' : 'pending')">
                                                <i class="fa-solid" :class="detailData.ultimo_diag.aprobado === 1 ? 'fa-circle-check' : (detailData.ultimo_diag.aprobado === 0 ? 'fa-circle-xmark' : 'fa-clock')"></i>
                                            </div>
                                            <div style="flex: 1;">
                                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                                    <span style="font-weight: 600; font-size: 14px; color: #111827;" x-text="'Diagnóstico #' + detailData.ultimo_diag.iddia"></span>
                                                    <span class="status-badge" :class="diagResultadoClass(detailData.ultimo_diag)" x-text="diagResultado(detailData.ultimo_diag)"></span>
                                                </div>
                                                <div style="display: flex; gap: 24px; font-size: 13px; color: #6b7280;">
                                                    <span><i class="fa-solid fa-calendar" style="margin-right: 4px;"></i> <span x-text="formatDate(detailData.ultimo_diag.fecdia)"></span></span>
                                                </div>
                                                <div style="margin-top: 8px; display: flex; gap: 24px; font-size: 12px; color: #9ca3af;">
                                                    <span x-show="detailData.ultimo_diag.persona"><i class="fa-solid fa-user" style="margin-right: 4px;"></i> <span x-text="detailData.ultimo_diag.persona ? (detailData.ultimo_diag.persona.nomper + ' ' + (detailData.ultimo_diag.persona.apeper || '')) : ''"></span></span>
                                                    <span x-show="detailData.ultimo_diag.fecvig"><i class="fa-solid fa-calendar-check" style="margin-right: 4px;"></i> Vigencia: <span x-text="formatDate(detailData.ultimo_diag.fecvig)"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!detailData?.ultimo_diag">
                                    <div style="text-align: center; padding: 24px; color: #9ca3af;">
                                        <div class="diag-icon-box none" style="margin: 0 auto 12px;">
                                            <i class="fa-solid fa-stethoscope"></i>
                                        </div>
                                        <p style="font-size: 13px; margin: 0;">Este vehículo no tiene diagnósticos registrados.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>


                    {{-- ══ TAB: Historial de Movimientos ══ --}}
                    <template x-if="selectedTab === 'historial'">
                        <div class="form-section">
                            <h4 class="form-section-title"><i class="fa-solid fa-clock-rotate-left"></i> Historial de Movimientos</h4>
                            <template x-if="detailData?.historial?.length > 0">
                                <div class="vtimeline">
                                    <template x-for="(h, idx) in detailData.historial" :key="h.idhis">
                                        <div class="vtimeline-item">
                                            {{-- Dot color: sistema=cyan, first=primary, else muted --}}
                                            <div class="vtimeline-icon" :class="h.es_sistema ? 'tl-system' : (idx === 0 ? '' : 'tl-muted')"></div>
                                            <div class="vtimeline-date">
                                                <i class="fa-regular fa-calendar"></i>
                                                <span x-text="h.created_at ? formatDateTime(h.created_at) : 'Sin fecha'"></span>
                                            </div>
                                            <div class="vtimeline-card">
                                                <h5 class="vtimeline-title" x-text="h.accion"></h5>
                                                <p class="vtimeline-desc" x-text="h.descripcion"></p>
                                                <div class="vtimeline-user">
                                                    <i class="fa-solid" :class="h.es_sistema ? 'fa-desktop' : 'fa-user'"></i>
                                                    <span x-text="h.persona ? (h.persona.nomper + ' ' + (h.persona.apeper || '')) : (h.es_sistema ? 'Sistema Automático' : 'Usuario')"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!detailData?.historial?.length">
                                <div style="text-align: center; padding: 32px; color: #9ca3af;">
                                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.25;"></i>
                                    <p style="font-size: 14px; font-weight: 500; margin: 0 0 4px 0; color: #6b7280;">Sin movimientos registrados</p>
                                    <p style="font-size: 13px; margin: 0;">Los cambios y eventos del vehículo aparecerán aquí.</p>
                                </div>
                            </template>
                            {{-- Footer del historial --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(0,0,0,0.06);" x-show="detailData?.historial?.length > 0">
                                <span style="font-size: 13px; color: #6b7280;">Mostrando el historial completo del vehículo</span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Card Footer: Edición de vínculo (solo Admin/Digitador) --}}
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                <div class="card-footer" x-show="editandoEmpresa" x-cloak style="flex-direction: column; align-items: stretch;">
                    <p style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 10px 0;">
                        <i class="fa-solid fa-pen" style="color: #f59e0b; margin-right: 6px;"></i>Cambiar empresa vinculada
                    </p>
                    <div style="display: flex; gap: 12px; align-items: flex-end;">
                        <div class="form-group" style="flex: 1;">
                            <label>Empresa</label>
                            <select x-model="editForm.idemp" style="height: 40px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; font-size: 14px; width: 100%; outline: none; font-family: inherit;">
                                <option value="">Sin empresa</option>
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->idemp }}">{{ $emp->razsoem }} — NIT {{ $emp->nonitem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="vbtn vbtn-secondary" @click="cancelEditEmpresa()" :disabled="editSaving" style="height: 40px;">Cancelar</button>
                        <button class="vbtn vbtn-primary" @click="saveEditEmpresa()" :disabled="editSaving" style="height: 40px;">
                            <i class="fa-solid fa-check" x-show="!editSaving"></i>
                            <i class="fa-solid fa-spinner fa-spin" x-show="editSaving"></i>
                            <span x-text="editSaving ? 'Guardando...' : 'Guardar'"></span>
                        </button>
                    </div>
                </div>
                @endif
            </div>

            {{-- ── RIGHT: Company Summary Card (reference-based) ── --}}
            <div class="vcard company-summary-card">
                <template x-if="selectedVehiculo.empresa">
                    <div style="display: flex; flex-direction: column; height: 100%;">
                        {{-- Header grande con logo + nombre + NIT + badge --}}
                        <div class="cscard-header">
                            <div class="cscard-logo"><i class="fa-solid fa-building-columns"></i></div>
                            <div class="cscard-title-area">
                                <h2 class="cscard-name" x-text="selectedVehiculo.empresa.razsoem"></h2>
                                <div class="cscard-nit-status">
                                    <div class="cscard-nit">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <span x-text="'NIT: ' + (selectedVehiculo.empresa.nonitem || 'N/A')"></span>
                                    </div>
                                    <span class="cscard-badge"><i class="fa-solid fa-circle" style="font-size: 6px; margin-right: 4px;"></i> Activa</span>
                                </div>
                            </div>
                        </div>

                        {{-- Cuerpo con stats reales --}}
                        <div class="cscard-body">
                            <div class="cscard-stats">
                                {{-- Vehículos Vinculados: conteo instantáneo desde array cliente --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-car-front"></i> Vehículos Vinculados</div>
                                    <div class="stat-value" x-text="vehiculosEnEmpresa(selectedVehiculo.idemp)"></div>
                                </div>
                                {{-- Personas Vinculadas: propietarios + conductores únicos de vehículos de la empresa --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-users"></i> Personas Vinculadas</div>
                                    <div class="stat-value" x-text="detailData?.empresa_stats?.personas_vinculadas ?? '—'"></div>
                                </div>
                                {{-- Total Diagnósticos: conteo real de diags de vehículos de la empresa --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-stethoscope"></i> Total Diagnósticos</div>
                                    <div class="stat-value" x-text="detailData?.empresa_stats?.total_diagnosticos ?? '—'"></div>
                                </div>
                                {{-- Gerente --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-user-tie"></i> Gerente</div>
                                    <div class="stat-value normal" x-text="selectedVehiculo.empresa.nomger || 'N/A'"></div>
                                </div>
                                {{-- Dirección --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-location-dot"></i> Dirección</div>
                                    <div class="stat-value normal" style="max-width: 140px; text-align: right; font-size: 13px;" x-text="selectedVehiculo.empresa.direm || 'N/A'"></div>
                                </div>
                                {{-- Teléfono --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-phone"></i> Teléfono</div>
                                    <div class="stat-value normal" x-text="selectedVehiculo.empresa.telem || 'N/A'"></div>
                                </div>
                                {{-- Email --}}
                                <div class="stat-row">
                                    <div class="stat-label"><i class="fa-solid fa-envelope"></i> Email</div>
                                    <div class="stat-value normal" style="max-width: 140px; text-align: right; font-size: 12px; word-break: break-all;" x-text="selectedVehiculo.empresa.emaem || 'N/A'"></div>
                                </div>
                            </div>


                        {{-- Footer con acciones --}}
                        <div class="cscard-footer" style="flex-direction: column;">
                            <button @click="activeView = 'perfil'" class="vbtn vbtn-outline" style="width: 100%; justify-content: center;">
                                <i class="fa-solid fa-building"></i> Ir a perfil de empresa
                            </button>
                            <button @click="activeView = 'reporte'" class="vbtn vbtn-primary" style="width: 100%; justify-content: center; background-color: #0b3a5a; color: white;">
                                <i class="fa-solid fa-chart-line"></i> Reporte de Flota
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Estado sin empresa --}}
                <template x-if="!selectedVehiculo.empresa">
                    <div style="padding: 48px 24px; text-align: center; color: #9ca3af;">
                        <i class="fa-solid fa-building-circle-xmark" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.25;"></i>
                        <p style="font-size: 14px; font-weight: 500; margin: 0 0 4px 0; color: #6b7280;">Sin empresa vinculada</p>
                        <p style="font-size: 13px; margin: 0;">Este vehículo no tiene empresa asignada.</p>
                    </div>
                </template>
            </div>
        </div>
        </div> {{-- Fin Vista 1: vehiculos --}}

        {{-- ═══════════════════════════════════════ --}}
        {{-- VISTA 2: PERFIL DE EMPRESA              --}}
        {{-- ═══════════════════════════════════════ --}}
        <div x-show="activeView === 'perfil'" style="display: none; padding-top: 12px;">
            <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
            <style>
                .pe-wrapper {
                    --background: #f4f6f8;
                    --foreground: #111827;
                    --primary: #0b1f38;
                    --primary-hover: #16325b;
                    --border: #e5e7eb;
                    --card-bg: #ffffff;
                    --success: #10b981;
                    --success-bg: #d1fae5;
                    --success-text: #065f46;
                    --warning: #f59e0b;
                    --warning-bg: #fef3c7;
                    --warning-text: #92400e;
                    --muted-bg: #f3f4f6;
                    --muted-text: #4b5563;
                }
                .pe-wrapper {
                    font-family: var(--font-family-body, 'Inter', system-ui, sans-serif);
                    background-color: var(--background);
                    color: var(--foreground);
                    min-height: calc(100vh - 100px);
                    padding: 32px 40px;
                    display: flex;
                    flex-direction: column;
                    gap: 24px;
                    border-radius: 12px;
                }

                .pe-wrapper .btn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    padding: 10px 16px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: 0.2s;
                    border: 1px solid transparent;
                    white-space: nowrap;
                }
                .pe-wrapper .btn-primary { background-color: var(--primary); color: #ffffff; }
                .pe-wrapper .btn-primary:hover { background-color: var(--primary-hover); }
                .pe-wrapper .btn-secondary { background-color: #ffffff; border-color: #d1d5db; color: #374151; }
                .pe-wrapper .btn-secondary:hover { background-color: #f9fafb; }
                .pe-wrapper .btn-outline { background-color: transparent; border-color: #d1d5db; color: #374151; }
                .pe-wrapper .btn-outline:hover { background-color: #f9fafb; border-color: #9ca3af; }

                .pe-wrapper .card {
                    background: var(--card-bg);
                    border-radius: 12px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.025);
                    border: 1px solid var(--border);
                    overflow: hidden;
                }

                .pe-wrapper .page-header { display: flex; justify-content: space-between; align-items: flex-start; }
                .pe-wrapper .page-title { font-size: 24px; font-weight: 700; color: var(--foreground); margin: 0 0 6px 0; }
                .pe-wrapper .page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }
                .pe-wrapper .profile-title-group { display: flex; align-items: center; gap: 20px; }
                .pe-wrapper .company-logo-large {
                    width: 64px; height: 64px; background-color: #ffffff; border: 1px solid var(--border);
                    border-radius: 12px; display: flex; align-items: center; justify-content: center;
                    font-size: 32px; color: var(--primary); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                }
                .pe-wrapper .header-actions { display: flex; gap: 12px; }

                .pe-wrapper .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
                .pe-wrapper .info-card { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
                .pe-wrapper .info-card-header {
                    display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 600;
                    color: #111827; border-bottom: 1px solid var(--border); padding-bottom: 16px;
                }
                .pe-wrapper .info-card-header iconify-icon { color: var(--primary); font-size: 20px; }
                .pe-wrapper .info-list { display: flex; flex-direction: column; gap: 16px; }
                .pe-wrapper .info-item { display: flex; align-items: flex-start; gap: 12px; }
                .pe-wrapper .info-item-icon { color: #6b7280; font-size: 18px; margin-top: 1px; }
                .pe-wrapper .info-item-content { display: flex; flex-direction: column; gap: 4px; }
                .pe-wrapper .info-item-label { font-size: 12px; color: #6b7280; font-weight: 500; }
                .pe-wrapper .info-item-value { font-size: 14px; color: #111827; font-weight: 500; }
                .pe-wrapper .stat-highlight {
                    display: flex; align-items: center; justify-content: space-between; padding: 12px 16px;
                    background-color: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;
                }
                .pe-wrapper .stat-highlight-label { font-size: 13px; color: #4b5563; font-weight: 500; }
                .pe-wrapper .stat-highlight-value { font-size: 22px; font-weight: 700; color: var(--primary); }

                .pe-wrapper .full-width-card .card-header { padding: 0 24px; border-bottom: 1px solid var(--border); }
                .pe-wrapper .tabs { display: flex; gap: 24px; }
                .pe-wrapper .tab {
                    padding: 16px 0; background: none; border: none; font-size: 14px; font-weight: 500;
                    color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent;
                    margin-bottom: -1px; transition: 0.2s;
                }
                .pe-wrapper .tab:hover { color: #111827; }
                .pe-wrapper .tab.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 600; }

                .pe-wrapper .filters-bar { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
                .pe-wrapper .search-box { position: relative; flex: 1; max-width: 400px; }
                .pe-wrapper .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 18px; }
                .pe-wrapper .search-box input {
                    width: 100%; height: 40px; padding-left: 38px; padding-right: 12px;
                    border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none;
                }
                .pe-wrapper .search-box input:focus { border-color: var(--primary); }
                .pe-wrapper .filter-group { display: flex; gap: 12px; }
                .pe-wrapper .filter-group select {
                    height: 40px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 36px 0 12px;
                    font-size: 14px; outline: none; background-color: #ffffff;
                    appearance: none;
                    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                    background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
                }
                .pe-wrapper .table-container { overflow-x: auto; }
                .pe-wrapper .data-table { width: 100%; border-collapse: collapse; text-align: left; }
                .pe-wrapper .data-table th {
                    padding: 14px 24px; font-size: 12px; font-weight: 600; color: #6b7280;
                    text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border);
                    background-color: #f9fafb; white-space: nowrap;
                }
                .pe-wrapper .data-table td {
                    padding: 16px 24px; font-size: 14px; color: #374151; border-bottom: 1px solid var(--border);
                    vertical-align: middle; white-space: nowrap;
                }
                .pe-wrapper .data-table tbody tr:hover { background-color: #f9fafb; }
                .pe-wrapper .company-cell, .pe-wrapper .vehicle-cell { display: flex; flex-direction: column; gap: 4px; }
                .pe-wrapper .company-cell .company-name { font-weight: 600; color: #111827; }
                .pe-wrapper .company-cell .company-nit { font-size: 12px; color: #6b7280; }
                .pe-wrapper .vehicle-cell .vehicle-model { font-size: 12px; color: #6b7280; }
                .pe-wrapper .plate-badge {
                    background-color: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; padding: 4px 8px;
                    border-radius: 6px; font-weight: 600; font-family: monospace; font-size: 13px; display: inline-block;
                }
                .pe-wrapper .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
                .pe-wrapper .status-badge.success { background-color: var(--success-bg); color: var(--success-text); }
                .pe-wrapper .status-badge.warning { background-color: var(--warning-bg); color: var(--warning-text); }
                .pe-wrapper .status-badge.inactive { background-color: var(--muted-bg); color: var(--muted-text); }
                .pe-wrapper .actions-cell { display: flex; justify-content: flex-end; gap: 8px; }
                .pe-wrapper .icon-btn {
                    width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
                    border-radius: 6px; border: none; background: transparent; color: #6b7280; cursor: pointer; transition: 0.2s;
                }
                .pe-wrapper .icon-btn:hover { background: #e5e7eb; color: #111827; }
                
                @media (max-width: 1024px) {
                    .pe-wrapper .stats-grid { grid-template-columns: 1fr; }
                    .pe-wrapper { padding: 16px; }
                }
                @media (max-width: 768px) {
                    .pe-wrapper .page-header { flex-direction: column; gap: 16px; }
                    .pe-wrapper .filters-bar { flex-direction: column; align-items: stretch; }
                    .pe-wrapper .search-box { max-width: 100%; }
                    .pe-wrapper .filter-group { flex-wrap: wrap; }
                    .pe-wrapper .filter-group select { flex: 1; }
                    .pe-wrapper .tabs { flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 12px; }
                    .pe-wrapper .tab { border: none; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; }
                    .pe-wrapper .tab.active { background: var(--primary); color: white; }
                }
            </style>

            <template x-if="selectedVehiculo && selectedVehiculo.empresa">
                <main class="pe-wrapper">
                    <!-- BOTÓN VOLVER -->
                    <div>
                        <button @click="activeView = 'vehiculos'" class="btn btn-outline">
                            <iconify-icon icon="lucide:arrow-left"></iconify-icon> Volver a Vehículos
                        </button>
                    </div>

                    <div class="page-header">
                        <div class="profile-title-group">
                            <div class="company-logo-large">
                                <iconify-icon icon="lucide:building-2"></iconify-icon>
                            </div>
                            <div>
                                <h1 class="page-title" x-text="selectedVehiculo.empresa.razsoem"></h1>
                                <p class="page-subtitle">
                                    <span x-text="'NIT: ' + selectedVehiculo.empresa.nonitem"></span> | Estado:
                                    <span style="color: var(--success); font-weight: 500">Activa</span>
                                </p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button @click="activeView = 'reporte'" class="btn btn-outline">
                                <iconify-icon icon="lucide:file-bar-chart"></iconify-icon> Reporte de Flota
                            </button>
                            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Empresa'))
                            <button class="btn btn-primary" @click="openEditPerfil">
                                <iconify-icon icon="lucide:edit"></iconify-icon> Editar Perfil
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Formulario Edición de Perfil (In-place) --}}
                    <div class="card p-6" x-show="editandoPerfilEmpresa" style="margin-bottom: 24px;">
                        <h4 style="font-weight: 600; margin-bottom: 16px; color: #111827;">Actualizar Datos Corportativos</h4>
                        <div class="form-grid cols-2" style="margin-bottom: 16px;">
                            <div class="form-group">
                                <label>Razón Social <span style="color:red">*</span></label>
                                <input type="text" x-model="perfilForm.razsoem" class="input-control" placeholder="Nombre completo" />
                            </div>
                            <div class="form-group">
                                <label>NIT <span style="color:red">*</span></label>
                                <input type="text" x-model="perfilForm.nonitem" class="input-control" placeholder="Ej. 900.000.000-1" />
                            </div>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" x-model="perfilForm.direm" class="input-control" placeholder="Dirección principal" />
                            </div>
                            <div class="form-group">
                                <label>Teléfono Corporativo</label>
                                <input type="text" x-model="perfilForm.telem" class="input-control" placeholder="Número de contacto" />
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Nombre del Gerente / Representante <span style="color:red">*</span></label>
                                <input type="text" x-model="perfilForm.nomger" class="input-control" placeholder="Nombre de quien representa" />
                            </div>
                        </div>
                        <div style="display: flex; gap: 12px; justify-content: flex-end;">
                            <button @click="cancelEditPerfil" class="vbtn vbtn-secondary">Cancelar</button>
                            <button @click="saveEditPerfil" class="vbtn vbtn-primary" :disabled="perfilSaving">
                                <span x-show="!perfilSaving">Guardar Cambios</span>
                                <span x-show="perfilSaving"><i class="fa-solid fa-spinner fa-spin"></i> Actualizando...</span>
                            </button>
                        </div>
                    </div>

                    <div class="stats-grid" x-show="!editandoPerfilEmpresa">
                        <div class="card info-card">
                            <div class="info-card-header">
                                <iconify-icon icon="lucide:contact-2"></iconify-icon> Información de Contacto
                            </div>
                            <div class="info-list">
                                <div class="info-item">
                                    <iconify-icon icon="lucide:map-pin" class="info-item-icon"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Dirección Principal</span>
                                        <span class="info-item-value" x-text="selectedVehiculo.empresa.direm || 'N/A'"></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <iconify-icon icon="lucide:phone" class="info-item-icon"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Teléfono</span>
                                        <span class="info-item-value" x-text="selectedVehiculo.empresa.telem || 'N/A'"></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <iconify-icon icon="lucide:mail" class="info-item-icon"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Correo Electrónico</span>
                                        <span class="info-item-value" x-text="selectedVehiculo.empresa.emaem || 'N/A'"></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <iconify-icon icon="lucide:user" class="info-item-icon"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Representante Legal</span>
                                        <span class="info-item-value" x-text="selectedVehiculo.empresa.nomger || 'N/A'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card info-card">
                            <div class="info-card-header">
                                <iconify-icon icon="lucide:bar-chart-3"></iconify-icon> Resumen de Flota
                            </div>
                            <div class="info-list">
                                <div class="stat-highlight">
                                    <span class="stat-highlight-label">Vehículos Vinculados</span>
                                    <span class="stat-highlight-value" x-text="detailData?.empresa_stats?.vehiculos_vinculados || 0"></span>
                                </div>
                                <div class="stat-highlight">
                                    <span class="stat-highlight-label">Conductores / Propietarios</span>
                                    <span class="stat-highlight-value" x-text="detailData?.empresa_stats?.personas_vinculadas || 0"></span>
                                </div>
                                <div class="info-item" style="margin-top: 8px" x-show="detailData?.empresa_stats?.total_diagnosticos > 0">
                                    <iconify-icon icon="lucide:activity" class="info-item-icon" style="color: var(--primary)"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Total Diagnósticos</span>
                                        <span class="info-item-value" style="color: var(--primary); font-weight: 600;" x-text="detailData.empresa_stats.total_diagnosticos"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card info-card">
                            <div class="info-card-header">
                                <iconify-icon icon="lucide:building"></iconify-icon> Detalles Empresariales
                            </div>
                            <div class="info-list">
                                <div class="info-item">
                                    <iconify-icon icon="lucide:id-card" class="info-item-icon"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Razón Social</span>
                                        <span class="info-item-value" x-text="selectedVehiculo.empresa.razsoem || 'N/A'"></span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <iconify-icon icon="lucide:tag" class="info-item-icon"></iconify-icon>
                                    <div class="info-item-content">
                                        <span class="info-item-label">Sigla / Abreviatura</span>
                                        <span class="info-item-value" x-text="selectedVehiculo.empresa.abremp || 'N/A'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card full-width-card" id="vehicles-assigned-card">
                        <div class="card-header">
                            <div class="tabs">
                                <button class="tab" :class="{ 'active': peSubTab === 'vehiculos' }" @click="peSubTab = 'vehiculos'">
                                    Vehículos Asignados (<span x-text="vehiculos.filter(v => v.idemp === selectedVehiculo.empresa.idemp).length"></span>)
                                </button>
                                <button class="tab" :class="{ 'active': peSubTab === 'propietarios' }" @click="peSubTab = 'propietarios'">
                                    Propietarios (<span x-text="pePropietarios.length"></span>)
                                </button>
                                <button class="tab" :class="{ 'active': peSubTab === 'conductores' }" @click="peSubTab = 'conductores'">
                                    Conductores (<span x-text="peConductores.length"></span>)
                                </button>
                            </div>
                        </div>

                        <!-- VISTA: VEHÍCULOS -->
                        <div x-show="peSubTab === 'vehiculos'">
                            <div style="padding: 24px; border-bottom: 1px solid var(--border)">
                            <div class="filters-bar">
                                <div class="search-box">
                                    <iconify-icon icon="lucide:search" class="search-icon"></iconify-icon>
                                    <input type="text" x-model="peSearchTerm" placeholder="Buscar vehículo por placa o modelo..."/>
                                </div>
                                <div class="filter-group">
                                    <select x-model="peFilterEstado">
                                        <option value="Todos">Diagnóstico: Todos</option>
                                        <option value="Aprobado">Aprobados</option>
                                        <option value="No Aprobado">No Aprobados</option>
                                        <option value="Pendiente">Pendientes</option>
                                    </select>
                                    <button class="btn btn-outline" @click="peSearchTerm = ''; peFilterEstado = 'Todos';">
                                        <iconify-icon icon="lucide:filter-x"></iconify-icon> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Placa / Vehículo</th>
                                        <th>Clase</th>
                                        <th>Color</th>
                                        <th>Cilindraje</th>
                                        <th>Diagnóstico</th>
                                        <th class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="peFilteredVehiculos.length === 0">
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 32px; color: #6b7280;">No se encontraron vehículos que coincidan con la búsqueda.</td>
                                        </tr>
                                    </template>
                                    <template x-for="v in peFilteredVehiculos.slice((peVehCurrentPage - 1) * peVehPerPage, peVehCurrentPage * peVehPerPage)" :key="v.idveh">
                                        <tr>
                                            <td>
                                                <div class="vehicle-cell">
                                                    <div class="plate-badge" x-text="v.placaveh"></div>
                                                    <span class="vehicle-model" x-text="vehiculoModelo(v)"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="company-cell">
                                                    <span class="company-name" x-text="v.clase?.nomval || 'N/A'"></span>
                                                </div>
                                            </td>
                                            <td x-text="v.colveh || 'N/A'"></td>
                                            <td x-text="v.cilveh || 'N/A'"></td>
                                            <td><span class="status-badge" :class="getVehiculoEstadoPerfil(v.idveh).clase" x-text="getVehiculoEstadoPerfil(v.idveh).texto"></span></td>
                                            <td class="actions-cell">
                                                <button class="icon-btn" title="Ver detalle en lista general" @click="search = v.placaveh; activeView = 'vehiculos'; selectVehiculo(v);">
                                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            
                            {{-- Pagination --}}
                            <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-white border-t border-slate-200 gap-4" x-show="peFilteredVehiculos.length > 0">
                                <div style="color: #64748b; font-size: 14px;">
                                    Mostrando <span style="font-weight: 500; color: #0f172a;" x-text="(peVehCurrentPage - 1) * peVehPerPage + (peFilteredVehiculos.length > 0 ? 1 : 0)"></span> a <span style="font-weight: 500; color: #0f172a;" x-text="Math.min(peVehCurrentPage * peVehPerPage, peFilteredVehiculos.length)"></span> de <span style="font-weight: 500; color: #0f172a;" x-text="peFilteredVehiculos.length"></span> resultados
                                </div>
                                <div class="inline-flex border border-slate-200 rounded-md overflow-x-auto shadow-sm max-w-full" x-show="Math.ceil(peFilteredVehiculos.length / peVehPerPage) > 1" x-cloak>
                                    <button @click="peVehCurrentPage > 1 ? peVehCurrentPage-- : null" :disabled="peVehCurrentPage === 1" 
                                            :style="peVehCurrentPage === 1 ? 'padding: 8px 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; border-right: 1px solid #e2e8f0; color: #64748b; cursor: pointer;'">
                                        <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                                    </button>
                                    <template x-for="(page, index) in buildPaginationArray(peFilteredVehiculos.length, peVehPerPage, peVehCurrentPage)" :key="index">
                                        <button @click="page !== '...' ? peVehCurrentPage = page : null" 
                                                :disabled="page === '...'"
                                                x-text="page" 
                                                :style="page === peVehCurrentPage ? 'padding: 8px 14px; background: #f1f5f9; border-right: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; cursor: default;' : (page === '...' ? 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #94a3b8; cursor: default;' : 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #64748b; cursor: pointer;')">
                                        </button>
                                    </template>
                                    <button @click="peVehCurrentPage < Math.ceil(peFilteredVehiculos.length / peVehPerPage) ? peVehCurrentPage++ : null" :disabled="peVehCurrentPage === Math.ceil(peFilteredVehiculos.length / peVehPerPage)" 
                                            :style="peVehCurrentPage === Math.ceil(peFilteredVehiculos.length / peVehPerPage) ? 'padding: 8px 12px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; color: #64748b; cursor: pointer;'">
                                        <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- VISTA: CONDUCTORES -->
                        <div x-show="peSubTab === 'conductores'" style="display: none;">
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Identificación</th>
                                            <th>Nombre Conductor</th>
                                            <th>Teléfono</th>
                                            <th>Vehículos Asignados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="peConductores.length === 0">
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 32px; color: #6b7280;">No hay conductores vinculados a los vehículos de esta empresa.</td>
                                            </tr>
                                        </template>
                                        <template x-for="c in peConductores.slice((peCondCurrentPage - 1) * peCondPerPage, peCondCurrentPage * peCondPerPage)" :key="c.idper">
                                            <tr>
                                                <td>
                                                    <span style="font-family: monospace; color: var(--primary);" x-text="c.ndocper"></span>
                                                </td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <iconify-icon icon="lucide:user" style="color: #6b7280;"></iconify-icon>
                                                        <span style="font-weight: 500; color: #111827;" x-text="c.nomper + ' ' + (c.apeper || '')"></span>
                                                    </div>
                                                </td>
                                                <td x-text="c.telper || 'N/A'"></td>
                                                <td>
                                                    <div style="display: flex; gap: 4px; flex-wrap: wrap; max-width: 250px;">
                                                        <template x-for="v in vehiculos.filter(vh => vh.idemp === selectedVehiculo.empresa.idemp && vh.cond === c.idper)">
                                                            <span class="plate-badge" style="font-size: 11px; padding: 2px 6px;" x-text="v.placaveh"></span>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                
                                {{-- Pagination --}}
                                <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-white border-t border-slate-200 gap-4" x-show="peConductores.length > 0">
                                    <div style="color: #64748b; font-size: 14px;">
                                        Mostrando <span style="font-weight: 500; color: #0f172a;" x-text="(peCondCurrentPage - 1) * peCondPerPage + (peConductores.length > 0 ? 1 : 0)"></span> a <span style="font-weight: 500; color: #0f172a;" x-text="Math.min(peCondCurrentPage * peCondPerPage, peConductores.length)"></span> de <span style="font-weight: 500; color: #0f172a;" x-text="peConductores.length"></span> resultados
                                    </div>
                                    <div class="inline-flex border border-slate-200 rounded-md overflow-x-auto shadow-sm max-w-full" x-show="Math.ceil(peConductores.length / peCondPerPage) > 1" x-cloak>
                                        <button @click="peCondCurrentPage > 1 ? peCondCurrentPage-- : null" :disabled="peCondCurrentPage === 1" 
                                                :style="peCondCurrentPage === 1 ? 'padding: 8px 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; border-right: 1px solid #e2e8f0; color: #64748b; cursor: pointer;'">
                                            <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                                        </button>
                                        <template x-for="(page, index) in buildPaginationArray(peConductores.length, peCondPerPage, peCondCurrentPage)" :key="index">
                                            <button @click="page !== '...' ? peCondCurrentPage = page : null" 
                                                    :disabled="page === '...'"
                                                    x-text="page" 
                                                    :style="page === peCondCurrentPage ? 'padding: 8px 14px; background: #f1f5f9; border-right: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; cursor: default;' : (page === '...' ? 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #94a3b8; cursor: default;' : 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #64748b; cursor: pointer;')">
                                            </button>
                                        </template>
                                        <button @click="peCondCurrentPage < Math.ceil(peConductores.length / peCondPerPage) ? peCondCurrentPage++ : null" :disabled="peCondCurrentPage === Math.ceil(peConductores.length / peCondPerPage)" 
                                                :style="peCondCurrentPage === Math.ceil(peConductores.length / peCondPerPage) ? 'padding: 8px 12px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; color: #64748b; cursor: pointer;'">
                                            <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VISTA: PROPIETARIOS -->
                        <div x-show="peSubTab === 'propietarios'" style="display: none;">
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Identificación</th>
                                            <th>Nombre Propietario</th>
                                            <th>Teléfono</th>
                                            <th>Vehículos Asociados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="pePropietarios.length === 0">
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 32px; color: #6b7280;">No hay propietarios registrados para los vehículos de esta empresa.</td>
                                            </tr>
                                        </template>
                                        <template x-for="p in pePropietarios.slice((pePropCurrentPage - 1) * pePropPerPage, pePropCurrentPage * pePropPerPage)" :key="p.idper">
                                            <tr>
                                                <td>
                                                    <span style="font-family: monospace; color: var(--primary);" x-text="p.ndocper"></span>
                                                </td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <iconify-icon icon="lucide:user-check" style="color: #6b7280;"></iconify-icon>
                                                        <span style="font-weight: 500; color: #111827;" x-text="p.nomper + ' ' + (p.apeper || '')"></span>
                                                    </div>
                                                </td>
                                                <td x-text="p.telper || 'N/A'"></td>
                                                <td>
                                                    <div style="display: flex; gap: 4px; flex-wrap: wrap; max-width: 250px;">
                                                        <template x-for="v in vehiculos.filter(vh => vh.idemp === selectedVehiculo.empresa.idemp && vh.prop === p.idper)">
                                                            <span class="plate-badge" style="font-size: 11px; padding: 2px 6px;" x-text="v.placaveh"></span>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                
                                {{-- Pagination --}}
                                <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-white border-t border-slate-200 gap-4" x-show="pePropietarios.length > 0">
                                    <div style="color: #64748b; font-size: 14px;">
                                        Mostrando <span style="font-weight: 500; color: #0f172a;" x-text="(pePropCurrentPage - 1) * pePropPerPage + (pePropietarios.length > 0 ? 1 : 0)"></span> a <span style="font-weight: 500; color: #0f172a;" x-text="Math.min(pePropCurrentPage * pePropPerPage, pePropietarios.length)"></span> de <span style="font-weight: 500; color: #0f172a;" x-text="pePropietarios.length"></span> resultados
                                    </div>
                                    <div class="inline-flex border border-slate-200 rounded-md overflow-x-auto shadow-sm max-w-full" x-show="Math.ceil(pePropietarios.length / pePropPerPage) > 1" x-cloak>
                                        <button @click="pePropCurrentPage > 1 ? pePropCurrentPage-- : null" :disabled="pePropCurrentPage === 1" 
                                                :style="pePropCurrentPage === 1 ? 'padding: 8px 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; border-right: 1px solid #e2e8f0; color: #64748b; cursor: pointer;'">
                                            <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                                        </button>
                                        <template x-for="(page, index) in buildPaginationArray(pePropietarios.length, pePropPerPage, pePropCurrentPage)" :key="index">
                                            <button @click="page !== '...' ? pePropCurrentPage = page : null" 
                                                    :disabled="page === '...'"
                                                    x-text="page" 
                                                    :style="page === pePropCurrentPage ? 'padding: 8px 14px; background: #f1f5f9; border-right: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; cursor: default;' : (page === '...' ? 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #94a3b8; cursor: default;' : 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #64748b; cursor: pointer;')">
                                            </button>
                                        </template>
                                        <button @click="pePropCurrentPage < Math.ceil(pePropietarios.length / pePropPerPage) ? pePropCurrentPage++ : null" :disabled="pePropCurrentPage === Math.ceil(pePropietarios.length / pePropPerPage)" 
                                                :style="pePropCurrentPage === Math.ceil(pePropietarios.length / pePropPerPage) ? 'padding: 8px 12px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; color: #64748b; cursor: pointer;'">
                                            <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
                </main>
            </template>

            <template x-if="!selectedVehiculo || !selectedVehiculo.empresa">
                <div style="padding: 48px; text-align: center; color: #9ca3af; background: #ffffff; border-radius: 12px;">
                    <iconify-icon icon="lucide:building" style="font-size: 48px; opacity: 0.2;"></iconify-icon>
                    <p style="margin-top: 16px; font-weight: 500;">Selecciona una empresa en el panel principal para ver su perfil.</p>
                </div>
            </template>
        </div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- VISTA 3: REPORTE DE FLOTA               --}}
        {{-- ═══════════════════════════════════════ --}}
        <div x-show="activeView === 'reporte'" style="display: none;">
            <style>
                .rf-card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                .rf-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; gap: 12px; }
                .rf-header-left h2 { font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px; }
                .rf-header-left p { font-size: 13px; color: #6b7280; margin: 0; }
                .rf-actions { display: flex; gap: 10px; flex-wrap: wrap; }
                .rf-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid transparent; transition: 0.2s; white-space: nowrap; }
                .rf-btn-outline { background: transparent; border-color: #d1d5db; color: #374151; }
                .rf-btn-outline:hover { background: #f9fafb; border-color: #9ca3af; }
                .rf-btn-primary { background: #0b3a5a; color: #fff; border-color: #0b3a5a; }
                .rf-btn-primary:hover { background: #082d46; }
                .rf-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
                .rf-filters { padding: 16px 24px; background: #f9fafb; border-bottom: 1px solid #f3f4f6; }
                .rf-filters-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
                .rf-filters-row + .rf-filters-row { margin-top: 10px; }
                .rf-filters label { font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 2px; display: block; }
                .rf-select { height: 34px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 28px 0 10px; font-size: 13px; background: #fff; outline: none; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 8px center; background-size: 14px; }
                .rf-select:focus { border-color: #0b3a5a; }
                .rf-input-date { height: 34px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 10px; font-size: 13px; font-family: inherit; outline: none; }
                .rf-input-date:focus { border-color: #0b3a5a; }
                .rf-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 16px 24px; }
                .rf-summary-card { padding: 14px; border-radius: 8px; text-align: center; }
                .rf-summary-card .num { font-size: 24px; font-weight: 700; line-height: 1.2; }
                .rf-summary-card .lbl { font-size: 11px; font-weight: 500; margin-top: 2px; }
                .rf-summary-card.total { background: #eff6ff; border: 1px solid #bfdbfe; }
                .rf-summary-card.total .num { color: #1d4ed8; }
                .rf-summary-card.total .lbl { color: #1e40af; }
                .rf-summary-card.ok { background: #f0fdf4; border: 1px solid #bbf7d0; }
                .rf-summary-card.ok .num { color: #15803d; }
                .rf-summary-card.ok .lbl { color: #166534; }
                .rf-summary-card.fail { background: #fef2f2; border: 1px solid #fecaca; }
                .rf-summary-card.fail .num { color: #dc2626; }
                .rf-summary-card.fail .lbl { color: #991b1b; }
                .rf-table-wrap { overflow-x: auto; }
                .rf-table { width: 100%; border-collapse: collapse; }
                .rf-table th { padding: 10px 16px; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; white-space: nowrap; text-align: left; }
                .rf-table td { padding: 12px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
                .rf-table tbody tr:hover { background: #f9fafb; }
                .rf-table .plate-badge { background: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; padding: 3px 7px; border-radius: 5px; font-weight: 600; font-family: monospace; font-size: 12px; display: inline-block; }
                .rf-table .badge { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 500; }
                .rf-table .badge.success { background: #dcfce7; color: #15803d; }
                .rf-table .badge.danger { background: #fef2f2; color: #dc2626; }
                .rf-table .badge.warning { background: #fef3c7; color: #92400e; }
                .rf-table .badge.inactive { background: #f3f4f6; color: #6b7280; }
                .rf-informe-btn { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; cursor: pointer; transition: 0.15s; }
                .rf-informe-btn.enabled { background: #eff6ff; color: #1d4ed8; }
                .rf-informe-btn.enabled:hover { background: #dbeafe; color: #1e40af; }
                .rf-informe-btn.disabled { background: #f3f4f6; color: #d1d5db; cursor: not-allowed; }
                .rf-footer { padding: 12px 24px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
                .rf-footer span { font-size: 12px; color: #9ca3af; }
                .rf-clear-btn { font-size: 12px; color: #6b7280; background: none; border: 1px solid #e5e7eb; border-radius: 5px; padding: 4px 10px; cursor: pointer; }
                .rf-clear-btn:hover { background: #f3f4f6; }
                @media (max-width: 768px) {
                    .rf-summary { grid-template-columns: 1fr; }
                    .rf-header { flex-direction: column; align-items: stretch; }
                    .rf-actions { justify-content: flex-end; }
                }
            </style>

            <div class="rf-card">
                {{-- Header --}}
                <div class="rf-header">
                    <div class="rf-header-left">
                        <h2>
                            <i class="fa-solid fa-chart-line" style="color: #0b3a5a;"></i>
                            Reporte de Diagnósticos
                        </h2>
                        <p>
                            <i class="fa-solid fa-building" style="margin-right: 3px;"></i>
                            <strong x-text="selectedVehiculo?.empresa?.razsoem || ''"></strong>
                            <span x-show="selectedVehiculo?.empresa?.nonitem"> — NIT: <span x-text="selectedVehiculo.empresa.nonitem"></span></span>
                        </p>
                    </div>
                    <div class="rf-actions">
                        <button @click="activeView = 'perfil'" class="rf-btn rf-btn-outline">
                            <i class="fa-solid fa-arrow-left"></i> Perfil
                        </button>
                        <button class="rf-btn rf-btn-primary" @click="exportarFlota()" :disabled="reportesExportables.length === 0">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span x-text="reportesExportables.length > 0 ? 'Imprimir / PDF (' + reportesExportables.length + ')' : 'Sin informes'"></span>
                        </button>
                    </div>
                </div>

                <template x-if="detailLoading">
                    <div style="text-align: center; padding: 48px; color: #9ca3af;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 24px; color: #0b1f38;"></i>
                        <p style="margin-top: 8px;">Cargando reportes...</p>
                    </div>
                </template>

                <template x-if="!detailLoading && (!detailData || !detailData.reporte_flota || detailData.reporte_flota.length === 0)">
                    <div style="text-align: center; padding: 48px;">
                        <i class="fa-solid fa-clipboard-list" style="font-size: 32px; color: #d1d5db; margin-bottom: 12px;"></i>
                        <p style="font-size: 14px; color: #6b7280; margin: 0;">No se encontraron diagnósticos finalizados para esta empresa.</p>
                    </div>
                </template>

                <template x-if="!detailLoading && detailData && detailData.reporte_flota && detailData.reporte_flota.length > 0">
                    <div>
                        {{-- Filtros --}}
                        <div class="rf-filters">
                            <div class="rf-filters-row">
                                <span style="font-size: 12px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Rápido:
                                </span>
                                <select x-model="reporteQuickAnio" @change="setQuickAnio($event.target.value)" class="rf-select">
                                    <option value="">Año</option>
                                    <template x-for="y in reporteAniosDisponibles" :key="y">
                                        <option :value="y" x-text="y"></option>
                                    </template>
                                </select>
                                <select x-model="reporteQuickMes" @change="setQuickMes($event.target.value)" class="rf-select">
                                    <option value="">Mes</option>
                                    <option value="1">Ene</option><option value="2">Feb</option><option value="3">Mar</option>
                                    <option value="4">Abr</option><option value="5">May</option><option value="6">Jun</option>
                                    <option value="7">Jul</option><option value="8">Ago</option><option value="9">Sep</option>
                                    <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dic</option>
                                </select>

                                <span style="color: #d1d5db; margin: 0 4px;">|</span>

                                <div style="display: flex; flex-direction: column;">
                                    <label>Desde</label>
                                    <input type="date" x-model="fechaInicio" @change="reporteQuickMes = ''; reporteQuickAnio = '';" class="rf-input-date">
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <label>Hasta</label>
                                    <input type="date" x-model="fechaFin" @change="reporteQuickMes = ''; reporteQuickAnio = '';" class="rf-input-date">
                                </div>

                                <button class="rf-clear-btn" @click="clearReporteFilters()" x-show="fechaInicio || fechaFin" style="align-self: flex-end;">
                                    <i class="fa-solid fa-xmark"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        {{-- Summary Cards --}}
                        <div class="rf-summary" x-show="reportesFiltrados.length > 0">
                            <div class="rf-summary-card total">
                                <div class="num" x-text="reporteResumen.total"></div>
                                <div class="lbl">Total Diagnósticos</div>
                            </div>
                            <div class="rf-summary-card ok">
                                <div class="num" x-text="reporteResumen.aprobados"></div>
                                <div class="lbl">Aprobados</div>
                            </div>
                            <div class="rf-summary-card fail">
                                <div class="num" x-text="reporteResumen.noAprobados"></div>
                                <div class="lbl">No Aprobados</div>
                            </div>
                        </div>

                        {{-- Empty state filtrado --}}
                        <template x-if="reportesFiltrados.length === 0">
                            <div style="text-align: center; padding: 40px; color: #6b7280;">
                                <i class="fa-solid fa-filter-circle-xmark" style="font-size: 24px; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                                No hay diagnósticos en el rango seleccionado.
                            </div>
                        </template>

                        {{-- Table --}}
                        <div class="rf-table-wrap" x-show="reportesFiltrados.length > 0">
                            <table class="rf-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Placa / Vehículo</th>
                                        <th>Resultado</th>
                                        <th>Inspector</th>
                                        <th>Ingeniero</th>
                                        <th>Parámetros</th>
                                        <th style="text-align: center;">Informe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="rep in reportesFiltrados.slice((peRepCurrentPage - 1) * peRepPerPage, peRepCurrentPage * peRepPerPage)" :key="rep.iddia">
                                        <tr>
                                            <td>
                                                <div style="font-weight: 500; color: #111827;" x-text="formatDateTime(rep.fecdia).split(',')[0]"></div>
                                                <div style="font-size: 11px; color: #9ca3af;" x-text="formatDateTime(rep.fecdia).split(',')[1] || ''"></div>
                                            </td>
                                            <td>
                                                <span class="plate-badge" x-text="rep.vehiculo?.placaveh || 'N/A'"></span>
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;" x-text="rep.vehiculo?.marca?.nommarlin || ''"></div>
                                            </td>
                                            <td>
                                                <span class="badge" :class="diagResultadoClass(rep)" x-text="diagResultado(rep)"></span>
                                            </td>
                                            <td>
                                                <span x-text="rep.inspector ? (rep.inspector.nomper + ' ' + (rep.inspector.apeper||'')) : '—'"></span>
                                            </td>
                                            <td>
                                                <span x-text="rep.ingeniero ? (rep.ingeniero.nomper + ' ' + (rep.ingeniero.apeper||'')) : '—'"></span>
                                            </td>
                                            <td>
                                                <div style="max-width: 180px; white-space: normal; line-height: 1.3; color: #6b7280; font-size: 12px;" x-text="paramResumen(rep)"></div>
                                            </td>
                                            <td style="text-align: center;">
                                                <template x-if="canExportDiag(rep)">
                                                    <a :href="exportUrl(rep.iddia)" target="_blank" class="rf-informe-btn enabled" title="Ver informe completo">
                                                        <i class="fa-solid fa-file-lines"></i>
                                                    </a>
                                                </template>
                                                <template x-if="!canExportDiag(rep)">
                                                    <span class="rf-informe-btn disabled" title="Informe no disponible">
                                                        <i class="fa-solid fa-file-circle-xmark"></i>
                                                    </span>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            
                            {{-- Pagination --}}
                            <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-white border-t border-slate-200 gap-4" x-show="reportesFiltrados.length > 0">
                                <div style="color: #64748b; font-size: 14px;">
                                    Mostrando <span style="font-weight: 500; color: #0f172a;" x-text="(peRepCurrentPage - 1) * peRepPerPage + (reportesFiltrados.length > 0 ? 1 : 0)"></span> a <span style="font-weight: 500; color: #0f172a;" x-text="Math.min(peRepCurrentPage * peRepPerPage, reportesFiltrados.length)"></span> de <span style="font-weight: 500; color: #0f172a;" x-text="reportesFiltrados.length"></span> resultados
                                </div>
                                <div class="inline-flex border border-slate-200 rounded-md overflow-x-auto shadow-sm max-w-full" x-show="Math.ceil(reportesFiltrados.length / peRepPerPage) > 1" x-cloak>
                                    <button @click="peRepCurrentPage > 1 ? peRepCurrentPage-- : null" :disabled="peRepCurrentPage === 1" 
                                            :style="peRepCurrentPage === 1 ? 'padding: 8px 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; border-right: 1px solid #e2e8f0; color: #64748b; cursor: pointer;'">
                                        <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                                    </button>
                                    <template x-for="(page, index) in buildPaginationArray(reportesFiltrados.length, peRepPerPage, peRepCurrentPage)" :key="index">
                                        <button @click="page !== '...' ? peRepCurrentPage = page : null" 
                                                :disabled="page === '...'"
                                                x-text="page" 
                                                :style="page === peRepCurrentPage ? 'padding: 8px 14px; background: #f1f5f9; border-right: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; cursor: default;' : (page === '...' ? 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #94a3b8; cursor: default;' : 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #64748b; cursor: pointer;')">
                                        </button>
                                    </template>
                                    <button @click="peRepCurrentPage < Math.ceil(reportesFiltrados.length / peRepPerPage) ? peRepCurrentPage++ : null" :disabled="peRepCurrentPage === Math.ceil(reportesFiltrados.length / peRepPerPage)" 
                                            :style="peRepCurrentPage === Math.ceil(reportesFiltrados.length / peRepPerPage) ? 'padding: 8px 12px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; color: #64748b; cursor: pointer;'">
                                        <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="rf-footer" x-show="reportesFiltrados.length > 0">
                            <span>
                                <i class="fa-solid fa-info-circle" style="margin-right: 3px;"></i>
                                <strong x-text="reportesFiltrados.length"></strong> diagnóstico(s)
                                <span x-show="fechaInicio || fechaFin"> en el rango seleccionado</span>
                                · <strong x-text="reportesExportables.length"></strong> exportable(s)
                            </span>
                            <span x-text="'Generado: ' + new Date().toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' })"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
@endsection
