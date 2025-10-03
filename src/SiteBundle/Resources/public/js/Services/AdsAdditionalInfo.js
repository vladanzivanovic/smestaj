import UserDashboardMapper from "../Mapper/UserDashboardMapper";
import DropZoneService from "../../../../../../app/Resources/public/js/Services/DropZoneService";

export default (() => {
    const Public = {}, Private = {};
    
    Private.editIndex = null;
    Private.mapper = new UserDashboardMapper();

    Public.rooms = [];

    Public.roomModalInit = () => {

        tjq('#save-additional').off().on('click touchend', () => {
            if (null != Private.editIndex) {
               Public.edit();
               return true;
            }
            Public.add();
        });

        tjq('.room-btn').on('click', (e) => {
            tjq(e.currentTarget).soapPopup({
                wrapId: "soap-popupbox",
            });
        });

        tjq(document).on('click touchend', '.remove-room', () => {
            var index = tjq(this).hasAndGetData('room');
            if (index < 0) {
               return false;
            }
            Public.remove(index);
        });

        Public.resetModal();
    };

    Public.resetModal = () => {
        Private.mapper.roomModal.find('input, textarea').each((index, element) => {
            tjq(element).val('');
        });
        DropZoneService().reset('additionalImages');
    };

    Public.setEditModal = (index, self) => {
        this.resetModal();

        self.soapPopup({
            wrapId: "soap-popupbox",
        });
        var documents = this.rooms[index].Media ? this.rooms[index].Media : JSON.parse(this.rooms[index].Documents);

        Private.editIndex = index;

        tjq('#room-title').text(Private.setRoomNo());

        tjq.each(this.rooms[index], function (key, value) {
            tjq('[name="'+ key +'"]', Public.roomModal).val(value);
        })
        dropZoneService.reset('additionalImages');
        dropZoneService.setFilesFromUrl(documents, 'additionalImages');
    };

    Public.setAddModal = () => {
        this.resetModal();
        Private.editIndex = null;

        tjq('#room-title').text(Private.setRoomNo());
    }

    Public.add = () => {
        var data = {};

        this.roomModal.find('input, textarea').each((index, element) => {
            if(tjq(this).attr('type') != 'file') {
                var name = tjq(element).attr('name'),
                    value = tjq(element).val();
                data[name] = value;
            }
        });

        data.Documents = JSON.stringify(dropZoneService.getFilesArray('additionalImages'));

        this.rooms.push(data);

        this.populateAndRenderHtml(this.rooms);
    };

    Public.edit = () => {
        var data = {};

        this.roomModal.find('input, textarea').each((index, element) => {
            if(tjq(element).attr('type') != 'file') {
                var name = tjq(element).attr('name'),
                    value = tjq(element).val();
                data[name] = value;
            }
        });

        data.isModify = true;
        data.Documents = JSON.stringify(dropZoneService.getFilesArray('additionalImages'));

        delete data.Media;

        Public.rooms[Private.editIndex] = data;

        Private.editIndex = null;
    };
    
    Public.remove = function (index) {
        this.rooms[index].isDeleted = true;

        if(!this.rooms[index].InfoId) {
            this.rooms.splice(index, 1);
        }

        this.populateAndRenderHtml(this.rooms);
    };

    Public.removeAll = () => {
        tjq.each(this.rooms, (key) => {
            this.remove(key);
        })
    };

    Public.populateAndRenderHtml = (data) => {
        this.rooms = data;
        tjq('#room-holder').empty();

        var roomNo = 0;

        tjq.each(this.rooms, (index, room) => {
            if (!room.hasOwnProperty('isDeleted')) {
                roomNo++;
                var divMain = tjq('<div>', {class: 'col-sm-6 col-md-3'}),
                    divInner = tjq('<div>', {class: 'icon-box style1 ads-room'}),
                    aTag = tjq('<a>', {
                        class: 'edit-room',
                        'data-room': index,
                        href: '#add-room',
                        text: 'Sala '+roomNo
                    }),
                    icon = tjq('<i>', {class: 'soap-icon-close remove-room', 'data-room': index});

                tjq('#room-holder').append(
                    divMain.append(
                        divInner.append(icon, aTag)
                    )
                );
            }
        });

        tjq(document).on('click touchend', '.edit-room', function (e) {
            Public.setEditModal(tjq(e.currentTarget).data('room'), tjq(e.currentTarget));
        });
    };

    Private.setRoomNo = () => {
        var counter = 1;

        for(var i = 0; i < Public.rooms.length; i++) {
            if (Private.editIndex && i >= Private.editIndex) {
                continue;
            }
            if(!Public.rooms[i].hasOwnProperty('isDeleted')) {
                counter++;
            }
        }

        return 'Sala '+ counter;
    };

    return Public;
});