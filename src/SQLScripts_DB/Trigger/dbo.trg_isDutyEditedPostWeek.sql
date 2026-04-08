USE [Allocate7]
GO
/****** Object:  Trigger [dbo].[isDutyEditedPostWeek]    Script Date: 04/04/2025 20:12:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCLTech
-- Create date: 26-03-2025
-- Description:	To update isDutyEditedPostWeek flag
-- =============================================
CREATE OR ALTER   TRIGGER [dbo].[isDutyEditedPostWeek]
   ON  [dbo].[Allocations] 
   AFTER UPDATE
AS 
BEGIN
	SET NOCOUNT ON;
	if exists(select 1 from deleted where isDutyEditedPostWeek = 0)
	BEGIN
		if((update(DutyName)) or (update(StartTime)) or (update(EndTime)) or (update(Duration)) or (update(dutyBreakTime)) or (update(SchedulingTeamId)) 
		or (update(dutyColorId)) or (update(iDay)) or (update(IsNeedCovering)) or (update(IsOverrideOver12)) or (update(MarkedOvertime)) 
		or (update(MarkedOvertime)) or (update(dutyProgramId)) or (update(DutyProgramId2)) or (update(DutyProgramId3)) or (update(DutyProgramId4)) 
		or (update(DutyProgramId5)) or (update(DutyProgramId6)))
			update A set isDutyEditedPostWeek = 1, BaseCode = 0 from inserted i join Allocations A on A.ID = i.id where ISNULL(i.BaseCode,0) <> 1
			 
	END
	if exists(select 1 from inserted where BaseCode = 1)
	 begin
	  update A set BaseCode = 0 from inserted i join Allocations A on A.ID = i.id 
	 end
END
