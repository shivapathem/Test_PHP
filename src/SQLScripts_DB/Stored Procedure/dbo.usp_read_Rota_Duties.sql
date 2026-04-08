USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_read_Rota_Duties]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_read_Rota_Duties]
	-- Add the parameters for the stored procedure here
	@TeamID int,
	@RotaID int,
	@RotaWeek int,
	@WeekStartDate varchar(25)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
   
    -- Fetch statements for procedure here
	SELECT er.RotaID,
	er.RotaWeek,
	er.IsTemplate,
	er.AssignmentStartWeek as StartWeek,
	er.WeeksInRota,
	er.MasterDutyID,
	er.DutyName,
	er.SchedulingPersonID,
	er.DOTW,
	er.Duration,
	er.StartTime,
	er.EndTime,
	er.SchedulingTeamId,
	er.IsHomeTeam,
	er.DutyColourID,
	er.DisplayName,
	er.NetLogin,
	er.StaffNumber,
	er.StaffTextColour,
	er.SortCode,
	er.AssignmentEndWeek as EndWeek,
	er.DutyColorID
	FROM Exported_rota as er (NOLOCK)
	WHERE er.SchedulingTeamId = @TeamID 
	AND er.RotaWeek = @RotaWeek
	AND er.RotaID = @RotaID
	AND convert(date,@WeekStartDate) between isnull(er.DutyStartDate,convert(date,@WeekStartDate))
	and isnull(er.DutyEndDate,convert(date,@WeekStartDate))
	and convert(date,@WeekStartDate) between er.TeamJoinDate
	and isnull(er.TeamJoinEndDate,convert(date,@WeekStartDate))
END
'
EXEC dbo.sp_executesql @strSQL

GO