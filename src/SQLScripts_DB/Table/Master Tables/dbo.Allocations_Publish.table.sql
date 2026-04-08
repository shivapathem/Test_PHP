USE [Allocate7]
GO


SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO

IF NOT  EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[Allocations_Publish]'))


CREATE TABLE [dbo].[Allocations_Publish](
	[AllocateInstanceID] [int] NOT NULL,
	[DepartmentID] [smallint] NOT NULL,
	[AllocationID] [int] NOT NULL,
	[StaffNumber] [nvarchar](10) NULL,
	[DutyName] [nvarchar](100) NULL,
	[Duration] [float] NULL,
	[WeekNumber] [int] NOT NULL,
	[iDay] [smallint] NOT NULL,
	[StartTime] [float] NULL,
	[EndTime] [float] NULL,
	[ActingGrade] [int] NULL,
	[SortCode] [nvarchar](30) NULL,
	[LeaveID] [int] NULL,
	[ManualERR] [decimal](5, 2) NULL,
	[DutyComments] [varchar](max) NULL,
	[BaseCode] [int] NULL,
	[BackColour] [bigint] NULL,
	[FontColour] [bigint] NULL,
	[PersonComments] [varchar](max) NULL,
	[AdhocDuty] [bit] NULL,
	[MarkedOvertime] [bit] NULL,
	[MarkedPTExtraDay] [bit] NULL,
	[MarkedCompLeave] [bit] NULL,
	[MarkedSickness] [bit] NULL,
	[ManualOTAmount] [decimal](4, 2) NULL,
	[ManualOTExcBreaksAmount] [decimal](4, 2) NULL,
	[UnAllocated] [bit] NULL,
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[SchedulingTeamId] [int] NULL,
	[SchedulingPersonID] [int] NULL,
	[DutyDate] [datetime] NULL,
	[StartDate] [datetime] NULL,
	[EndDate] [datetime] NULL,
	[isAttention] [bit] NULL,
	[isRequest] [bit] NULL,
	[aftermidnight] [bit] NULL,
	[dutyProgramId] [int] NULL,
	[dutyBreakTime] [int] NULL,
	[dutyColorId] [int] NULL,
	[isPublished] [int] NULL,
	[IsHomeTeam] [int] NULL,
	[MarkWiad] [int] NULL,
	[MarkActual] [int] NULL,
	[isEdited] [int] NULL,
	[MannualOThours] [int] NULL,
	[IsActive] [int] NULL,
	[MarkWTD] [int] NULL,
	[WTDComments] [varchar](1000) NULL,
	[isEditable] [bit] NULL,
	[OrigAllocationID] [int] NULL,
	[MasterDutyId] [int] NULL,
	[isActiveDuty] [int] NULL,
	[isCompareEdited] [bit] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

SET ANSI_PADDING OFF
GO


