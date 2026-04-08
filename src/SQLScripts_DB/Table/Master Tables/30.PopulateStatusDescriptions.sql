USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Allocations

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('Allocations','AL_AllocationsID',0,NULL,'Created','Week Created',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('Allocations','AL_AllocationsID',1,NULL,'Published','Week Published',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('Allocations','AL_AllocationsID',2,NULL,'Freelancer','Freelancer No publish required',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('Allocations','AL_AllocationsID',9,NULL,'Deleted','Week Deleted',1,getutcdate())

-- AllocationsScheduledPersons

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_WIADStatus',0,NULL,'Normal','Performing Normal Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_WIADStatus',1,NULL,'WIAD','Marked As WIAD',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_WIADStatus',2,NULL,'Actual','Marked as Actual',1,getutcdate())

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_LeaveStatus',1,NULL,'Leave','Leave on a Duty Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_LeaveStatus',2,NULL,'OFF Leave','Leave on a unassigned Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_LeaveStatus',3,NULL,'Sick','Sick on a Duty Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_LeaveStatus',4,NULL,'U-Sick','Sick on a unassigned Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_LeaveStatus',5,NULL,'-Sick','Sick on a Misc Duty Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_LeaveStatus',6,NULL,'Absent','Markes as Absent',1,getutcdate())

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_UnderElevenBreakStatus',0,NULL,'Normal','No undereleven break',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_UnderElevenBreakStatus',1,NULL,'UnderElevenBreak','UnderElevenBreak',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_UnderElevenBreakStatus',2,NULL,'UnderElevenBreakOverride','UnderElevenBreakOverride',1,getutcdate())

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',0,NULL,'NoOverTwleve','Normal Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',1,NULL,'MarkedOverTwelve','Normal Over Twleve',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',2,NULL,'ApprovedOverTweleve','Over Twelve Approved',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',3,NULL,'OverseasOverTwleve','OverseasOverTwleve',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',4,NULL,'ApprovedOverseasOverTwleve','Approved OverseasOverTwleve',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',9,NULL,'UnApproved','Un Approved Normal OverTwelve ',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsScheduledPersons','ASP_OverTwelveStatus',8,NULL,'UnApprovedOverSeas12','Un Approved Overseas OverTwelve ',1,getutcdate())


-- UserDetails

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('UserDetails','UD_Status',1,NULL,'Active','Active User',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('UserDetails','UD_Status',9,NULL,'Deleted','User Deleted',1,getutcdate())

-- AllocationsDuties

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',1,NULL,'MasterDuty','Master Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',2,NULL,'MiscDuty','Misc Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',6,NULL,'AdHocDuty','Adhoc Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',7,NULL,'Unassigned','No Duty assigned',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',8,NULL,'Leave','Leave/Absent/Sick',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',9,NULL,'UnassignedWithAttributes','No Duty assigned but there are duty attributes',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyType',10,NULL,'UnassignedJob','To map UnassignedJob',1,getutcdate())

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyStatus',0,NULL,'Unallocated','Unassigned Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyStatus',1,NULL,'Allocated','Allocated Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsDuties','AD_DutyStatus',9,NULL,'Deleted','Deleted Duty',1,getutcdate())

-- AllocationsJobs

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsJobs','AJ_JobStatus',0,NULL,'Unallocated','Unassigned Job',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsJobs','AJ_JobStatus',1,NULL,'Allocated','Allocated Job',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('AllocationsJobs','AJ_JobStatus',9,NULL,'Deleted','Deleted Job',1,getutcdate())


-- MasterDuties

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('MasterDuties','DutyTypeID',1,NULL,'MasterDuty','Master Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('MasterDuties','DutyTypeID',2,NULL,'MiscDuty','Misc Duty',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('MasterDuties','DutyTypeID',6,NULL,'AdHocDuty','Adhoc Duty',1,getutcdate())

-- LeaveApplications

INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('LeaveApplications','LeaveTypeID',1,NULL,'Leave','Leave on a Duty Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('LeaveApplications','LeaveTypeID',2,NULL,'OFF Leave','Leave on a unassigned Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('LeaveApplications','LeaveTypeID',3,NULL,'Sick','Sick on a Duty Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('LeaveApplications','LeaveTypeID',4,NULL,'U-Sick','Sick on a unassigned Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('LeaveApplications','LeaveTypeID',5,NULL,'-Sick','Sick on a Misc Duty Day',1,getutcdate())
INSERT INTO StatusDesriptions( SD_TableName,SD_ColumnName,SD_ColumnValueINT,SD_ColumnValueCHAR,SD_StatusName,SD_StatusDescription,SD_CreatedBy,SD_CreatedDate)
VALUES ('LeaveApplications','LeaveTypeID',6,NULL,'PDL','PDL',1,getutcdate())


