USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

 IF NOT  EXISTS (SELECT 1 FROM sys.objects 
    WHERE object_id = OBJECT_ID(N'[dbo].[Allocations_Removed]'))
  BEGIN
		CREATE TABLE Allocations_Removed (
			[AllocateInstanceID] [int] NOT NULL,
			[DepartmentID] [smallint] NOT NULL,
			[AllocationID] [int] NOT NULL,
			[StaffNumber] [nvarchar](10) NULL,
			[DutyName] [nvarchar](100) NULL,
			[Duration] [int] NULL,
			[WeekNumber] [int] NOT NULL,
			[iDay] [smallint] NOT NULL,
			[StartTime] [int] NULL,
			[EndTime] [int] NULL,
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
			[ID] [int] NOT NULL,
			[SchedulingTeamId] [int] NULL,
			[SchedulingPersonID] [int] NULL,
			[DutyDate] [date] NULL,
			[StartDate] [datetime] NULL,
			[EndDate] [datetime] NULL,
			[isPublished] [bit] NULL,
			[IsHomeTeam] [bit] NULL,
			[MarkWiad] [bit] NULL,
			[MarkActual] [bit] NULL,
			[aftermidnight] [bit] NULL,
			[isAttention] [bit] NULL,
			[isRequest] [bit] NULL,
			[dutyProgramId] [int] NULL,
			[dutyBreakTime] [int] NULL,
			[dutyColorId] [int] NULL,
			[MannualOThours] [int] NULL,
			[isEdited] [bit] NULL,
			[MasterDutyId] [int] NULL,
			[isActive] [bit] NULL,
			[isActiveDuty] [bit] NULL,
			[isEditable] [bit] NULL,
			[OrigAllocationID] [int] NULL,
			[MarkWTD] [bit] NULL,
			[WTDComments] [varchar](500) NULL,
			[isCompareEdited] [bit] NULL,
			[DutyTeamID] [int] NULL,
			[PlannedDuration] [int] NULL,
			[MarkOverTwelve] [smallint] NULL,
			[OverTwelveHrs] [int] NULL,
			[IsOverseasOverTwelve] [bit] NULL,
			[IsUnderElevenBreak] [bit] NULL,
			[CalculatedUnderElevenHrs] [int] NULL,
			[IsUnderElevenBreakOverride] [bit] NULL,
			[OverrideUnderElevenHrs] [int] NULL,
			[UnderElevenComment] [nvarchar](500) NULL,
			[CreatedBy] [int] NULL,
			[CreatedDate] [datetime] NULL,
			[UpdatedBy] [int] NULL,
			[UpdatedDate] [datetime] NULL,
		 CONSTRAINT [PK_Allocations_Removed] PRIMARY KEY CLUSTERED 
		(
			[ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

  END
GO

