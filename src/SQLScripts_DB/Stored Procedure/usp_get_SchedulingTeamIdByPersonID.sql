USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_SchedulingTeamIdByPersonID]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_SchedulingTeamIdByPersonID]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_get_SchedulingTeamIdByPersonID] 
	-- Add the parameters for the stored procedure here
	@startdate varchar(30),
	@scheduledpersonid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	SELECT top (1) * from ScheduledPeople sp
						INNER JOIN ScheduledPersonTeam_LINK spl on spl.ScheduledPersonID = sp.ScheduledPersonID
							where sp.ScheduledPersonID = @scheduledpersonid and isnull(convert(datetime,EndDate,110),''9999-01-01'') >= convert(datetime,convert(varchar,GETDATE(),110),110)
							and convert(datetime,StartDate,110) <= convert(datetime,convert(varchar(30),@startdate,110),110)
							order by spl.StartDate DESC
END
'
EXEC dbo.sp_executesql @strSQL

GO
