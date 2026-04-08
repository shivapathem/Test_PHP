USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_Mod_MaintainPrefilledCharge]    Script Date: 20/01/2022 19:47:51 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_Mod_MaintainPrefilledCharge]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_Mod_MaintainPrefilledCharge]
	-- Add the parameters for the stored procedure here
	@UserId int,
	@ChargingId int,
	@MaintainCharging int,
	@SchedulingTeamId int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
		if (@maintainCharging = 1)
		BEGIN
			if EXISTS (select 1 from PrefilledChargeDetails_Link where UserId = @UserId and SchedulingTeamId = @SchedulingTeamId)
			BEGIN
				update [dbo].[PrefilledChargeDetails_Link] set ChargingId = @chargingId where UserId = @UserId and IsPrefilled = 1 and SchedulingTeamId = @SchedulingTeamId;
			END
			else
			BEGIN
				insert into PrefilledChargeDetails_Link (ChargingId,IsPrefilled,UserId,CreatedDate,SchedulingTeamId)
			                    values (@chargingId,@maintainCharging,@UserId,getdate(),@SchedulingTeamId);
			END
		END
		ELSE
		BEGIN
            Delete from [dbo].[PrefilledChargeDetails_Link] where ChargingId = @chargingId and UserId = @UserID and IsPrefilled = 1 and SchedulingTeamId = @SchedulingTeamId;
		END
END
'

EXEC dbo.sp_executesql @strSQL

GO
